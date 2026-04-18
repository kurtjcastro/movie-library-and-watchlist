-- kc784 | 4/17
-- creates movies table to store api + movie data
CREATE TABLE IF NOT EXISTS `Movies` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `api_id` VARCHAR(20) DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `release_year` INT DEFAULT NULL,
    `image_url` VARCHAR(500) DEFAULT NULL,
    `title_type` VARCHAR(50) DEFAULT NULL,
    `is_api` TINYINT(1) NOT NULL DEFAULT 0,
    `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`api_id`)
);