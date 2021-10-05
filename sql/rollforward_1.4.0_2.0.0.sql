#-----------------------------------------------------------------------------------------------------------------------
#--
#-- System      : SenDb
#--
#-- Filename    : rollforward_1.4.0_2.0.0.sql
#--
#-- Description : Migration script
#--
#--               1.4.0    -->    2.0.0
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
        SELECT 'This script can only run on a version 1.4.0 database' AS result
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

    IF (currentVersion <> '1.4.0') THEN
        SELECT 'This script can only run on a version 1.4.0 database' AS result
        UNION
        SELECT 'If you are certain you want to run this script, modify the source to bypass this check'
        UNION
        SELECT 'Latest version in `version` is not suitable for this script';

        LEAVE migrate;
    END IF;

                                                            #-----------------------------------------------------------
                                                            #-- Version is correct - migrate
                                                            #-----------------------------------------------------------
    RENAME TABLE barony TO barony_archive_1_4_0;

                                                            #-----------------------------------------------------------
                                                            #-- Log deployment
                                                            #-----------------------------------------------------------
    INSERT INTO version (version) VALUES ('2.0.0');

    SELECT 'Rollforward complete. Now at version 2.0.0.' AS result;

END //

DELIMITER ;

CALL spMigrate();

DROP PROCEDURE IF EXISTS spMigrate;
