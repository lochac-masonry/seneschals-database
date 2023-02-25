#-----------------------------------------------------------------------------------------------------------------------
#--
#-- System      : SenDb
#--
#-- Filename    : rollback_2.1.0_3.0.0.sql
#--
#-- Description : Migration script
#--
#--               2.1.0    <--    3.0.0
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
    ALTER TABLE scagroup
    DROP COLUMN emailDomain,
    ADD COLUMN scaname      VARCHAR(64) NOT NULL DEFAULT '',
    ADD COLUMN realname     VARCHAR(64) NOT NULL DEFAULT '',
    ADD COLUMN `address`    VARCHAR(64) NOT NULL DEFAULT '',
    ADD COLUMN postcode     INT         NOT NULL DEFAULT '0',
    ADD COLUMN phone        VARCHAR(32) NOT NULL DEFAULT '',
    ADD COLUMN email        VARCHAR(64) NOT NULL DEFAULT '',
    ADD COLUMN warrantstart DATE        NULL,
    ADD COLUMN warrantend   DATE        NULL,
    ADD COLUMN memnum       VARCHAR(8)  NOT NULL DEFAULT '',
    ADD COLUMN usevirtuser  TINYINT     NOT NULL DEFAULT '0';

    UPDATE scagroup
    INNER JOIN scagroup_archive_2_1_0 AS archive ON archive.id = scagroup.id
    SET
        scagroup.scaname      = archive.scaname,
        scagroup.realname     = archive.realname,
        scagroup.`address`    = archive.`address`,
        scagroup.postcode     = archive.postcode,
        scagroup.phone        = archive.phone,
        scagroup.email        = archive.email,
        scagroup.warrantstart = archive.warrantstart,
        scagroup.warrantend   = archive.warrantend,
        scagroup.memnum       = archive.memnum,
        scagroup.usevirtuser  = archive.usevirtuser;

    DROP TABLE scagroup_archive_2_1_0;

                                                            #-----------------------------------------------------------
                                                            #-- Log deployment
                                                            #-----------------------------------------------------------
    INSERT INTO version (version) VALUES ('2.1.0');

    SELECT 'Rollback complete. Now at version 2.1.0.' AS result;

END //

DELIMITER ;

CALL spMigrate();

DROP PROCEDURE IF EXISTS spMigrate;
