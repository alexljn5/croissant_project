-- =============================================
-- FULLY FIXED SCHEMA CREATION SCRIPT
-- Cream-approved 🐰✨
-- =============================================

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Drop existing schemas to regenerate
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `school_sys`;
DROP SCHEMA IF EXISTS `croissantdb`;

-- -----------------------------------------------------
-- Schema croissantdb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `croissantdb` DEFAULT CHARACTER SET utf8;
USE `croissantdb`;

-- -----------------------------------------------------
-- Table: account
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `croissantdb`.`account` (
  `account_id` INT NOT NULL COMMENT '6-digit random ID',
  `creation_time` TIME NOT NULL,
  `first_name` VARCHAR(80) NOT NULL,
  `last_name` VARCHAR(80) NOT NULL,
  `password` VARCHAR(80) NOT NULL,
  `phone_number` VARCHAR(11) NOT NULL,
  `email` VARCHAR(45) NOT NULL,
  `gender` VARCHAR(10) NOT NULL,
  `is_teacher` TINYINT NOT NULL,
  `is_admin` TINYINT NOT NULL,
  `address` VARCHAR(45) NOT NULL,
  `postal_code` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `uk_email` (`email`),
  CONSTRAINT `chk_account_id_length` CHECK (`account_id` BETWEEN 100000 AND 999999)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: class
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `croissantdb`.`class` (
  `class_number` INT NOT NULL AUTO_INCREMENT,
  `class_type` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`class_number`)
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: student_ticket
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `croissantdb`.`student_ticket` (
  `ticket_id` INT NOT NULL AUTO_INCREMENT,
  `expiration_date` DATE NOT NULL,
  `creation_date` DATE NOT NULL,
  `description` TEXT NOT NULL COMMENT 'Stores the assignment description',
  `class_number` INT NOT NULL,
  PRIMARY KEY (`ticket_id`),
  INDEX `fk_student_ticket_class_idx` (`class_number` ASC) VISIBLE,
  CONSTRAINT `fk_student_ticket_class`
    FOREIGN KEY (`class_number`)
    REFERENCES `croissantdb`.`class` (`class_number`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: teacher_ticket
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `croissantdb`.`teacher_ticket` (
  `teacher_ticket_id` INT NOT NULL AUTO_INCREMENT,
  `expiration_date` DATE NOT NULL,
  `creation_date` DATE NOT NULL,
  `description` TEXT NOT NULL COMMENT 'Stores the teacher assignment/description',
  `class_number` INT NOT NULL,
  PRIMARY KEY (`teacher_ticket_id`),
  INDEX `fk_teacher_ticket_class_idx` (`class_number` ASC) VISIBLE,
  CONSTRAINT `fk_teacher_ticket_class`
    FOREIGN KEY (`class_number`)
    REFERENCES `croissantdb`.`class` (`class_number`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB;


-- -----------------------------------------------------
-- Table: teacher_has_student_ticket
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `croissantdb`.`teacher_has_student_ticket` (
  `teacher_account_id` INT NOT NULL,
  `teacher_teacher_ticket_id` INT NOT NULL,
  `student_ticket_id` INT NOT NULL,
  PRIMARY KEY (`teacher_account_id`, `teacher_teacher_ticket_id`, `student_ticket_id`),
  INDEX `fk_teacher_has_student_ticket_student_ticket_idx` (`student_ticket_id` ASC) VISIBLE,
  INDEX `fk_teacher_has_student_ticket_teacher_idx` (`teacher_account_id` ASC) VISIBLE,
  INDEX `fk_teacher_has_student_ticket_teacher_ticket_idx` (`teacher_teacher_ticket_id` ASC) VISIBLE,
  CONSTRAINT `fk_teacher_has_student_ticket_teacher`
    FOREIGN KEY (`teacher_account_id`)
    REFERENCES `croissantdb`.`account` (`account_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_teacher_has_student_ticket_teacher_ticket`
    FOREIGN KEY (`teacher_teacher_ticket_id`)
    REFERENCES `croissantdb`.`teacher_ticket` (`teacher_ticket_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_teacher_has_student_ticket_student_ticket`
    FOREIGN KEY (`student_ticket_id`)
    REFERENCES `croissantdb`.`student_ticket` (`ticket_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: account_has_student_ticket
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `croissantdb`.`account_has_student_ticket` (
  `account_id` INT NOT NULL,
  `student_ticket_id` INT NOT NULL,
  PRIMARY KEY (`account_id`, `student_ticket_id`),
  INDEX `fk_account_has_student_ticket_student_ticket_idx` (`student_ticket_id` ASC) VISIBLE,
  INDEX `fk_account_has_student_ticket_account_idx` (`account_id` ASC) VISIBLE,
  CONSTRAINT `fk_account_has_student_ticket_account`
    FOREIGN KEY (`account_id`)
    REFERENCES `croissantdb`.`account` (`account_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_account_has_student_ticket_student_ticket`
    FOREIGN KEY (`student_ticket_id`)
    REFERENCES `croissantdb`.`student_ticket` (`ticket_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: account_has_teacher_ticket
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `croissantdb`.`account_has_teacher_ticket` (
  `account_id` INT NOT NULL,
  `teacher_ticket_id` INT NOT NULL,
  PRIMARY KEY (`account_id`, `teacher_ticket_id`),
  INDEX `fk_account_has_teacher_ticket_teacher_ticket_idx` (`teacher_ticket_id` ASC) VISIBLE,
  INDEX `fk_account_has_teacher_ticket_account_idx` (`account_id` ASC) VISIBLE,
  CONSTRAINT `fk_account_has_teacher_ticket_account`
    FOREIGN KEY (`account_id`)
    REFERENCES `croissantdb`.`account` (`account_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_account_has_teacher_ticket_teacher_ticket`
    FOREIGN KEY (`teacher_ticket_id`)
    REFERENCES `croissantdb`.`teacher_ticket` (`teacher_ticket_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: account_has_class
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `croissantdb`.`account_has_class` (
  `account_id` INT NOT NULL,
  `class_number` INT NOT NULL,
  PRIMARY KEY (`account_id`, `class_number`),
  INDEX `fk_account_has_class_class_idx` (`class_number` ASC) VISIBLE,
  INDEX `fk_account_has_class_account_idx` (`account_id` ASC) VISIBLE,
  CONSTRAINT `fk_account_has_class_account`
    FOREIGN KEY (`account_id`)
    REFERENCES `croissantdb`.`account` (`account_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_account_has_class_class`
    FOREIGN KEY (`class_number`)
    REFERENCES `croissantdb`.`class` (`class_number`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: account_has_teacher_has_student_ticket
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `croissantdb`.`account_has_teacher_has_student_ticket` (
  `account_id` INT NOT NULL,
  `teacher_has_student_ticket_teacher_account_id` INT NOT NULL,
  `teacher_has_student_ticket_teacher_ticket_id` INT NOT NULL,
  `teacher_has_student_ticket_student_ticket_id` INT NOT NULL,
  PRIMARY KEY (`account_id`, `teacher_has_student_ticket_teacher_account_id`, `teacher_has_student_ticket_teacher_ticket_id`, `teacher_has_student_ticket_student_ticket_id`),
  INDEX `fk_acc_has_th_st_idx` (`teacher_has_student_ticket_teacher_account_id` ASC, `teacher_has_student_ticket_teacher_ticket_id` ASC, `teacher_has_student_ticket_student_ticket_id` ASC) VISIBLE,
  INDEX `fk_account_has_teacher_has_student_ticket_account_idx` (`account_id` ASC) VISIBLE,
  CONSTRAINT `fk_account_has_teacher_has_student_ticket_account`
    FOREIGN KEY (`account_id`)
    REFERENCES `croissantdb`.`account` (`account_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_account_has_teacher_has_student_ticket_teacher_has_student`
    FOREIGN KEY (`teacher_has_student_ticket_teacher_account_id`, `teacher_has_student_ticket_teacher_ticket_id`, `teacher_has_student_ticket_student_ticket_id`)
    REFERENCES `croissantdb`.`teacher_has_student_ticket` (`teacher_account_id`, `teacher_teacher_ticket_id`, `student_ticket_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Schema school_sys
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `school_sys`;
USE `school_sys`;

-- -----------------------------------------------------
-- Table: student_has_teacher_ticket
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `school_sys`.`student_has_teacher_ticket` (
  `student_account_id` INT NOT NULL COMMENT 'References croissantdb.account.account_id',
  `student_ticket_id` INT NOT NULL,
  `teacher_ticket_id` INT NOT NULL,
  PRIMARY KEY (`student_account_id`, `student_ticket_id`, `teacher_ticket_id`),
  INDEX `fk_student_has_teacher_ticket_teacher_ticket_idx` (`teacher_ticket_id` ASC) VISIBLE,
  INDEX `fk_student_has_teacher_ticket_student_idx` (`student_account_id` ASC) VISIBLE,
  CONSTRAINT `fk_student_has_teacher_ticket_student`
    FOREIGN KEY (`student_account_id`)
    REFERENCES `croissantdb`.`account` (`account_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_student_has_teacher_ticket_teacher_ticket`
    FOREIGN KEY (`teacher_ticket_id`)
    REFERENCES `croissantdb`.`teacher_ticket` (`teacher_ticket_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Restore SQL environment
-- -----------------------------------------------------
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;