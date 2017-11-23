CREATE TABLE patches_eng_deu (id SERIAL PRIMARY KEY, user_id INTEGER, group_id TEXT, old_entry TEXT, new_entry TEXT,
comment TEXT, flags TEXT, approved BOOLEAN, merged_into_tei BOOLEAN, creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE patches_eng_deu_index (keyword TEXT, keyword_unaccent TEXT, patch_id INTEGER);
CREATE INDEX patches_eng_deu_index_of_ids ON patches_eng_deu (id);
CREATE INDEX patches_eng_deu_index_of_keywords ON patches_eng_deu_index (keyword);
