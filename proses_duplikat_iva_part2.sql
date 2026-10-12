-- PART 2: min_stock + revert + repoint (part 1 sudah sukses: 12 parent + 18 child + backup)
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1051,'17564IVA-9',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='17564-9' LIMIT 1;
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1052,'17565IVA-9',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='17565-9' LIMIT 1;
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1053,'17568IVA-7',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='17568-7' LIMIT 1;
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1054,'17570IVA-9',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='17570-9' LIMIT 1;
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1055,'17572IVA-7',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='17572-7' LIMIT 1;
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1056,'18395IVA-9',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='18395-9' LIMIT 1;
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1057,'18396IVA-9',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='18396-9' LIMIT 1;
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1058,'18397IVA-9',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='18397-9' LIMIT 1;
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1059,'24442IVA-7',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='24442-7' LIMIT 1;
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1060,'24442IVA-9',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='24442-9' LIMIT 1;
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1061,'24628IVA-7',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='24628-7' LIMIT 1;
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1062,'24628IVA-9',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='24628-9' LIMIT 1;
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1063,'SJA 11894IVA-7',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='SJA 11894-7' LIMIT 1;
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1064,'SJA 11894IVA-9',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='SJA 11894-9' LIMIT 1;
INSERT INTO master_product_min_stocks (id,product_packaging_id,warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at)
SELECT 1065,'SJA 11940IVA-9',warehouse_id,unit_id,quantity,reserved_quantity,selling_price,created_at,updated_at FROM master_product_min_stocks WHERE product_packaging_id='SJA 11940-9' LIMIT 1;

-- Kembalikan produk LAMA ke nama awal
UPDATE master_products SET name=material_name WHERE id IN ('17564','17565','17568','17570','17572','18395','18396','18397','24442','24628','SJA 11940','SJA 11894');
UPDATE master_products_packaging SET name=material_name WHERE id IN ('17564-9','17565-9','17568-7','17568-9','17570-9','17572-7','17572-9','18395-9','18396-9','18397-7','18397-9','24442-7','24442-9','24628-7','24628-9','SJA 11940-9','SJA 11894-7','SJA 11894-9');

-- Repoint 15 SO item terlist ke produk IVA baru
UPDATE penjualan_so_item SET product_packaging_id='24442IVA-7' WHERE id IN (5062,6512);
UPDATE penjualan_so_item SET product_packaging_id='17564IVA-9' WHERE id=7615;
UPDATE penjualan_so_item SET product_packaging_id='17568IVA-7' WHERE id IN (16961,17470);
UPDATE penjualan_so_item SET product_packaging_id='17572IVA-7' WHERE id IN (17471,18821);
UPDATE penjualan_so_item SET product_packaging_id='18396IVA-9' WHERE id=18822;
UPDATE penjualan_so_item SET product_packaging_id='18395IVA-9' WHERE id=20353;
UPDATE penjualan_so_item SET product_packaging_id='24628IVA-9' WHERE id=21146;
UPDATE penjualan_so_item SET product_packaging_id='18397IVA-9' WHERE id=21566;
UPDATE penjualan_so_item SET product_packaging_id='17565IVA-9' WHERE id=21568;
UPDATE penjualan_so_item SET product_packaging_id='17570IVA-9' WHERE id=22302;
UPDATE penjualan_so_item SET product_packaging_id='SJA 11894IVA-9' WHERE id=22303;
UPDATE penjualan_so_item SET product_packaging_id='SJA 11940IVA-9' WHERE id=22836;

-- Repoint 15 DO item terlist ke produk IVA baru
UPDATE penjualan_do_item SET product_packaging_id='24442IVA-7' WHERE id IN (3544,4557);
UPDATE penjualan_do_item SET product_packaging_id='17564IVA-9' WHERE id=5342;
UPDATE penjualan_do_item SET product_packaging_id='17568IVA-7' WHERE id IN (14051,14446);
UPDATE penjualan_do_item SET product_packaging_id='17572IVA-7' WHERE id IN (14447,22827);
UPDATE penjualan_do_item SET product_packaging_id='18396IVA-9' WHERE id=22828;
UPDATE penjualan_do_item SET product_packaging_id='18395IVA-9' WHERE id=24132;
UPDATE penjualan_do_item SET product_packaging_id='24628IVA-9' WHERE id=24687;
UPDATE penjualan_do_item SET product_packaging_id='18397IVA-9' WHERE id=25035;
UPDATE penjualan_do_item SET product_packaging_id='17565IVA-9' WHERE id=25037;
UPDATE penjualan_do_item SET product_packaging_id='17570IVA-9' WHERE id=25596;
UPDATE penjualan_do_item SET product_packaging_id='SJA 11894IVA-9' WHERE id=25597;
UPDATE penjualan_do_item SET product_packaging_id='SJA 11940IVA-9' WHERE id=33198;
