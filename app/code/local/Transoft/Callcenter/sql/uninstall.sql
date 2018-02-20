-- add table prefix if you have one
DROP TABLE IF EXISTS transoft_callcenter_initiator_order;
DELETE FROM core_resource WHERE code = 'transoft_callcenter_setup';
DELETE FROM core_config_data WHERE path like 'transoft_callcenter/%';