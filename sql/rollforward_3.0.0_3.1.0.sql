#-----------------------------------------------------------------------------------------------------------------------
#--
#-- System      : SenDb
#--
#-- Filename    : rollforward_3.0.0_3.1.0.sql
#--
#-- Description : Migration script
#--
#--               3.0.0    -->    3.1.0
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
        SELECT 'This script can only run on a version 3.0.0 database' AS result
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

    IF (currentVersion <> '3.0.0') THEN
        SELECT 'This script can only run on a version 3.0.0 database' AS result
        UNION
        SELECT 'If you are certain you want to run this script, modify the source to bypass this check'
        UNION
        SELECT 'Latest version in `version` is not suitable for this script';

        LEAVE migrate;
    END IF;

                                                            #-----------------------------------------------------------
                                                            #-- Version is correct - migrate
                                                            #-----------------------------------------------------------

    #-- For all columns in all tables, find any latin1 stored as UTF8 and convert it to actual UTF8.
    DROP TABLE IF EXISTS temp_updateSql;

    #-- The hex literal is a regex to find any bytes between 0xA0 and 0xFF
    CREATE TEMPORARY TABLE temp_updateSql
    AS SELECT
        CONCAT(
            'UPDATE `', c.TABLE_NAME, '`
            SET
                `', c.COLUMN_NAME, '` = CONVERT(CAST(CONVERT(`', c.COLUMN_NAME, '` USING latin1) AS BINARY) USING utf8mb4)
            WHERE
                `', c.COLUMN_NAME, '` RLIKE CONVERT(0x5BA02DFF5D USING latin1)
            AND CONVERT(CAST(CONVERT(`', c.COLUMN_NAME, '` USING latin1) AS BINARY) USING utf8mb4) IS NOT NULL;
        ') AS sqlStatement
    FROM INFORMATION_SCHEMA.columns      AS c
    INNER JOIN INFORMATION_SCHEMA.tables AS t
    ON
        t.TABLE_SCHEMA = c.TABLE_SCHEMA
    AND t.TABLE_NAME   = c.TABLE_NAME
    WHERE
        t.TABLE_TYPE   = 'BASE TABLE'
    AND c.TABLE_SCHEMA = dbName
    AND c.CHARACTER_SET_NAME IS NOT NULL;

    SELECT sqlStatement FROM temp_updateSql;

    OPEN updateSqlCursor;

    updateLoop: LOOP
        FETCH updateSqlCursor INTO updateSql;
        IF cursorDone THEN
            LEAVE updateLoop;
        END IF;

        SET @updateSqlLocal = updateSql;
        PREPARE updateStatement FROM @updateSqlLocal;
        EXECUTE updateStatement;
        DEALLOCATE PREPARE updateStatement;
    END LOOP;

    CLOSE updateSqlCursor;
    DROP TABLE IF EXISTS temp_updateSql;

                                                            #-----------------------------------------------------------
                                                            #-- Log deployment
                                                            #-----------------------------------------------------------
    INSERT INTO version (version) VALUES ('3.1.0');

    SELECT 'Rollforward complete. Now at version 3.1.0.' AS result;

END //

DELIMITER ;

CALL spMigrate();

DROP PROCEDURE IF EXISTS spMigrate;
