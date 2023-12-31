#-----------------------------------------------------------------------------------------------------------------------
#--
#-- System      : SenDb
#--
#-- Filename    : rollforward_3.2.0_3.2.1.sql
#--
#-- Description : Migration script
#--
#--               3.2.0    -->    3.2.1
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
    DECLARE updateSql          TEXT;
    DECLARE updateSqlCursor    CURSOR FOR SELECT sqlStatement FROM temp_updateSql;
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
        SELECT 'This script can only run on a version 3.2.0 database' AS result
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

    IF (currentVersion <> '3.2.0') THEN
        SELECT 'This script can only run on a version 3.2.0 database' AS result
        UNION
        SELECT 'If you are certain you want to run this script, modify the source to bypass this check'
        UNION
        SELECT 'Latest version in `version` is not suitable for this script';

        LEAVE migrate;
    END IF;

                                                            #-----------------------------------------------------------
                                                            #-- Version is correct - migrate
                                                            #-----------------------------------------------------------

    UPDATE scagroup
    SET `state` = 'VIC'
    WHERE groupname = 'Bordescros';

                                                            #-----------------------------------------------------------
                                                            #-- Log deployment
                                                            #-----------------------------------------------------------
    INSERT INTO version (version) VALUES ('3.2.1');

    SELECT 'Rollforward complete. Now at version 3.2.1.' AS result;

END //

DELIMITER ;

CALL spMigrate();

DROP PROCEDURE IF EXISTS spMigrate;
