# Requirements:
# * python3-psycopg2 (tested on Ubuntu 16.04)

import os
from lxml import etree
from io import StringIO
import hashlib
import lxml.html
import codecs
import chardet


def teis2sqls():


    def tei2sql(input_file, output_dir):

        print("Converting "+input_file)

        dict_name = input_file.split("/")[-1].replace(".tei", "").replace("-", "_")
        tei_table="tei_"+dict_name

        # Read Entries from *.tei
        with open(input_file, "r") as f:
            tei = f.read()
        tei = tei.replace("""<?xml version="1.0" encoding="UTF-8"?>""", "")
        tei = tei.replace("xmlns=\"http://www.tei-c.org/ns/1.0\"", "")
        tei = tei.replace("xmlns:tei=\"http://www.tei-c.org/ns/1.0\"", "")
        parser = etree.XMLParser(remove_blank_text=True)
        tree = etree.parse(StringIO(tei), parser)
        entries = tree.xpath("/TEI/text/body/entry")

        # Write tei_*.sql
        sql = "CREATE TABLE "+tei_table+" (entry_hash TEXT UNIQUE, entry TEXT UNIQUE);\n"
        for e in entries:
            entry = etree.tostring(e, encoding="utf-8")
            entry = codecs.decode(entry).replace("'", "''")
            sql+= "INSERT INTO "+tei_table+" VALUES(md5('"+entry+"'),'"+entry+"');\n"
        sql+= "CREATE INDEX "+tei_table+"_index_of_entry_hashes ON "+tei_table+" (entry_hash);"
        with open(output_dir+"/"+tei_table+".sql", "w") as f:
           f.write(sql)

        # Write tei_*_index.sql
        index_sql = "CREATE TABLE "+tei_table+"_index (keyword CITEXT, keyword_unaccent CITEXT, entry_hash TEXT);\n"
        for e in entries:
            entry = etree.tostring(e, encoding="utf-8")
            entry = codecs.decode(entry).replace("'", "''")
            headwords = e.xpath("form/orth")
            for h in headwords:
                headword = h.text
                index_sql+= "INSERT INTO "+tei_table+"_index VALUES('"+headword+"', unaccent('"+headword+"'), md5('"+entry+"'));\n"
            senses = e.xpath("sense/cit/quote")
            for s in senses:
                sense = s.text
                index_sql+= "INSERT INTO "+tei_table+"_index VALUES('"+sense+"', unaccent('"+sense+"'), md5('"+entry+"'));\n"
        index_sql+="CREATE INDEX "+tei_table+"_index_of_keywords ON "+tei_table+"_index (keyword);"
        with open(output_dir+"/"+tei_table+"_index.sql", "w") as f:
            f.write(index_sql)


    tei_lst = os.listdir("./dotTeis")
    tei_lst.remove('.gitignore')
    for tei in tei_lst:
        tei2sql("./dotTeis/"+tei, "./teiTableSqls/")

    print("Creating create_patch_tables.sql")
    sql=""
    for tei in tei_lst:
        dict=tei.replace(".tei", "").replace("-", "_")
        sql+="CREATE TABLE patches_"+dict+""" (id SERIAL PRIMARY KEY, user_id INTEGER, group_id TEXT, old_entry TEXT, new_entry TEXT,
comment TEXT, old_flags TEXT, new_flags TEXT, approved BOOLEAN, merged_into_tei BOOLEAN, creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP);\n"""
        sql+="CREATE TABLE patches_"+dict+"_index (keyword TEXT, keyword_unaccent TEXT, patch_id INTEGER);\n"
        sql+="CREATE INDEX patches_"+dict+"_index_of_ids ON patches_"+dict+" (id);\n"
        sql+="CREATE INDEX patches_"+dict+"_index_of_keywords ON patches_"+dict+"_index (keyword);\n"

    with open("create_patch_tables.sql", "w") as f:
        f.write(sql)


def importSqls():

    os.system("psql -U fd -d freedict -f create_patch_tables.sql")
    sqls =os.listdir("teiTableSqls/")
    sqls.remove('.gitignore')
    for f in sqls:
        os.system("psql -U fd -d freedict -f teiTableSqls/"+f)
