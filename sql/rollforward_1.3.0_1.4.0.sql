#-----------------------------------------------------------------------------------------------------------------------
#--
#-- System      : SenDb
#--
#-- Filename    : rollforward_1.3.0_1.4.0.sql
#--
#-- Description : Migration script
#--
#--               1.3.0    -->    1.4.0
#--
#-----------------------------------------------------------------------------------------------------------------------

DROP PROCEDURE IF EXISTS spMigrate;

DELIMITER //

CREATE PROCEDURE spMigrate ()
MODIFIES SQL DATA
migrate:BEGIN
    DECLARE dbName             VARCHAR(64);
    DECLARE versionTableExists INT;
    DECLARE currentVersion     VARCHAR(10) DEFAULT 'none';
    DECLARE cursorDone         INT DEFAULT FALSE;
    DECLARE tableName          VARCHAR(64);
    DECLARE tablesToMigrate    CURSOR FOR SELECT TABLE_NAME FROM temp_tablesToMigrate;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET cursorDone = TRUE;

    SELECT
        DATABASE()
    FROM
        DUAL
    INTO
        dbName;

                                                            #-----------------------------------------------------------
                                                            #-- Check that current database is of the correct version
                                                            #-----------------------------------------------------------
    SELECT
        COUNT(1)
    FROM
        information_schema.TABLES
    WHERE
        TABLE_SCHEMA = dbName
    AND TABLE_NAME   = 'version'
    INTO
        versionTableExists;

    IF (versionTableExists = 0) THEN
        SELECT 'This script can only run on a version 1.3.0 database' AS result
        UNION
        SELECT 'If you are certain you want to run this script, modify the source to bypass this check'
        UNION
        SELECT 'Table `version` not found.';

        LEAVE migrate;
    END IF;

    SELECT
        version
    FROM
        version
    ORDER BY
        deployedDate DESC
    LIMIT 1
    INTO
        currentVersion;

    IF (currentVersion <> '1.3.0') THEN
        SELECT 'This script can only run on a version 1.3.0 database' AS result
        UNION
        SELECT 'If you are certain you want to run this script, modify the source to bypass this check'
        UNION
        SELECT 'Latest version in `version` is not suitable for this script';

        LEAVE migrate;
    END IF;

                                                            #-----------------------------------------------------------
                                                            #-- Version is correct - migrate
                                                            #-----------------------------------------------------------
    #-- Remove the invalid default values from the scagroup table (these were set on an old MySQL version).
    ALTER TABLE scagroup
    ALTER COLUMN warrantstart DROP DEFAULT,
    ALTER COLUMN warrantend   DROP DEFAULT,
    ALTER COLUMN lastreport   DROP DEFAULT;

    #-- Convert all MyISAM tables to InnoDB so that foreign keys are supported.
    DROP TABLE IF EXISTS temp_tablesToMigrate;

    CREATE TEMPORARY TABLE temp_tablesToMigrate
    AS SELECT
        TABLE_NAME
    FROM
        information_schema.TABLES
    WHERE
        TABLE_SCHEMA = dbName
    AND ENGINE       = 'MyISAM';

    SELECT
        TABLE_NAME AS `Migrating these tables to the InnoDB storage engine:`
    FROM
        temp_tablesToMigrate;

    OPEN tablesToMigrate;

    alterTableLoop: LOOP
        FETCH tablesToMigrate INTO tableName;
        IF cursorDone THEN
            LEAVE alterTableLoop;
        END IF;

        SET @alterTableSql = CONCAT('ALTER TABLE ', tableName, ' ENGINE = InnoDB;');
        PREPARE alterTableStatement FROM @alterTableSql;
        EXECUTE alterTableStatement;
        DEALLOCATE PREPARE alterTableStatement;
    END LOOP;

    CLOSE tablesToMigrate;
    DROP TABLE IF EXISTS temp_tablesToMigrate;

    #-- Create the event_attachment table.
    DROP TABLE IF EXISTS event_attachment;

    CREATE TABLE event_attachment (
        id       INT           NOT NULL AUTO_INCREMENT,
        event_id INT           NOT NULL,
        location VARCHAR(255)  NOT NULL,
        name     NVARCHAR(255) NOT NULL,
        size     INT           NOT NULL,
        deleted  TINYINT       NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        INDEX (event_id),
        FOREIGN KEY (event_id) REFERENCES events (eventid) ON DELETE RESTRICT ON UPDATE CASCADE
    );

                                                            #-----------------------------------------------------------
                                                            #-- Log deployment
                                                            #-----------------------------------------------------------
    INSERT INTO version (version) VALUES ('1.4.0');

    SELECT 'Rollforward complete. Now at version 1.4.0.' AS result;

END //

DELIMITER ;

CALL spMigrate();

DROP PROCEDURE IF EXISTS spMigrate;
