-- Drop the index.
ALTER TABLE  `ext_TableEdit_row` DROP INDEX  `row_data`;

-- Change the structure.
ALTER TABLE  `ext_TableEdit_row` CHANGE  `row_data`  `row_data` BLOB NOT NULL

--   mysql> SELECT MIN(size), MAX(size), STDDEV(size), AVG(size) 
--          FROM (
--                SELECT row_id, BIT_LENGTH(row_data) AS size 
--                FROM ext_TableEdit_row 
--                 ORDER BY size DESC
--               ) AS table1;
-- +-----------+-----------+--------------+-----------+
-- | MIN(size) | MAX(size) | STDDEV(size) | AVG(size) |
-- +-----------+-----------+--------------+-----------+
-- |        16 |     95688 |    3087.3015 | 2073.1288 |
-- +-----------+-----------+--------------+-----------+

-- Reindex with a length attribute IN BYTES.
--   700 bytes is a little larger than the average size in bits plus the stddev in bits. 
CREATE FULLTEXT INDEX `row_data` ON ext_TableEdit_row ( row_data(700) );