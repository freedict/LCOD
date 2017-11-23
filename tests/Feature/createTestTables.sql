CREATE TABLE tei_test_dict (entry_hash TEXT, entry TEXT);
CREATE TABLE tei_test_dict_index (keyword TEXT, keyword_unaccent TEXT, entry_hash TEXT);
CREATE TABLE patches_test_dict(id SERIAL PRIMARY KEY, user_id INTEGER, group_id TEXT, old_entry TEXT, new_entry TEXT,
comment TEXT, flags TEXT, approved BOOLEAN, merged_by_upstream BOOLEAN, creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE patches_test_dict_index (keyword TEXT, keyword_unaccent TEXT, patch_id INTEGER);
