
ALTER TABLE `queue_data` ADD `threshold_equal` INT NOT NULL ,
ADD `cost_neighbors` TEXT NOT NULL ,
ADD `cost_similarity` FLOAT NOT NULL ,
ADD `img_conv` TEXT NOT NULL ;

ALTER TABLE `queue_data` CHANGE `threshold_equal` `threshold_equal` INT( 11 ) NOT NULL DEFAULT '0',
CHANGE `cost_similarity` `cost_similarity` FLOAT NOT NULL DEFAULT '1.0' ;

ALTER TABLE `markers_ready` ADD `threshold_equal` INT NOT NULL DEFAULT '0',
ADD `cost_neighbors` TEXT NOT NULL ,
ADD `cost_similarity` FLOAT NOT NULL DEFAULT '1.0',
ADD `img_conv` TEXT NOT NULL ;

UPDATE `queue_data`
SET `cost_neighbors`="8.0,8.0:0;10.0,0.0:128", `img_conv`="1" ;

UPDATE `queue_data`
SET `cost_similarity`=1.0, `threshold_equal`=0 ;

UPDATE `markers_ready`
SET `cost_neighbors`="8.0,8.0:0;10.0,0.0:128", `img_conv`="1" ;

UPDATE `markers_ready`
SET `cost_neighbors`="8.0,8.0:0;10.0,0.0:64"
WHERE `module_type`=1 ;

UPDATE `queue_data`
SET `cost_neighbors`="8.0,8.0:0;10.0,0.0:64"
WHERE `id` IN (SELECT `id` FROM `process_queue` WHERE `module_type` = 1) ;
