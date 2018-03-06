#!/bin/bash

### PHP-Foundation (https://github.com/delight-im/PHP-Foundation)
### Copyright (c) delight.im (https://www.delight.im/)
### Licensed under the MIT License (https://opensource.org/licenses/MIT)

# Switch to the directory where the current script is located
cd "${BASH_SOURCE%/*}" || exit 1

# BEGIN CONSTANTS

# Parent directory of all backups
CONTAINER_DIRECTORY="./backups"
# Unique name for the current backup
BACKUP_NAME=$(date -u +'%Y%m%dT%H%M%SZ')
# Directory where the current backup will be stored
BACKUP_DIRECTORY="${CONTAINER_DIRECTORY}/${BACKUP_NAME}"
# Filename for the archive of all data to be backed up
DATA_ARCHIVE_FILENAME="data.tar.gz"
# Filename for the archive of all data in symmetrically encrypted form
DATA_ENCRYPTED_FILENAME="data.tar.gz.crypt"
# Filename for the archive of data from the file system
FILE_SYSTEM_ARCHIVE_FILENAME="file-system.tar"
# Filename for the archive of log files
LOGS_ARCHIVE_FILENAME="logs.tar"
# Filename for the temporary (and private) MySQL configuration
MYSQL_CONFIGURATION_FILENAME=".mysql.cnf"
# Filename for the database schema
DATABASE_SCHEMA_FILENAME="database-schema.sql"
# Filename for the database seeds
DATABASE_SEEDS_FILENAME="database-seeds.sql"
# Filename for the full database
DATABASE_FULL_FILENAME="database-full.sql"
# Filename for the key for symmetric encryption
SYMMETRIC_KEY_FILENAME="symmetric-key.bin"
# Filename for the symmetric key in asymmetrically encrypted form
SYMMETRIC_KEY_ENCRYPTED_FILENAME="symmetric-key.bin.crypt"
# Filename for the ultimate result of the current backup
BACKUP_FILENAME="${BACKUP_NAME}.tar"
# Path to the public key for asymmetric encryption
ASYMMETRIC_PUBLIC_KEY_FILENAME="asymmetric-key.public.pem"
# Filename for the exemplary script showing how decryption works
DECRYPTION_SAMPLE_SCRIPT_FILENAME="decrypt.sh"

# END CONSTANTS

# Announce that the backup process begins
echo "Creating backup"

# Verify that the source directory is a valid project root by looking for some important files and directories
if [ -d "app" ] && [ -f "index.php" ] && [ -d "public" ] && [ -d "vendor" ] && [ -d "views" ]; then
	echo " * Source directory successfully verified ..."
else
	echo " * Error: Source directory could not be verified"
	exit 2
fi

# Verify that the current script is executed with superuser privileges
if [[ $EUID > 0 ]]; then
	echo " * Error: Please run script with superuser privileges instead"
	exit 3
fi

# Verify that the configuration of the application exists and can be read
if [ -d "config" ] && [ -f "config/.env" ] && [ -r "config/.env" ]; then
	echo " * Configuration verified to be readable ..."
else
	echo " * Error: Configuration could not be found or accessed"
	exit 4
fi

# Verify that the parent directory for any backups exists and can be written to
if [ -d $CONTAINER_DIRECTORY ] && [ -w $CONTAINER_DIRECTORY ]; then
	echo " * Directory 'backups' exists and can be written to ..."
else
	echo " * Error: Directory 'backups' does not exist or cannot be written to"
	exit 5
fi

# Verify that the public key for asymmetric encryption exists and can be read
if [ "$1" != "unencrypted" ]; then
	if [ -f "${CONTAINER_DIRECTORY}/${ASYMMETRIC_PUBLIC_KEY_FILENAME}" ] && [ -r "${CONTAINER_DIRECTORY}/${ASYMMETRIC_PUBLIC_KEY_FILENAME}" ]; then
		echo " * Public key for asymmetric encryption found ..."
	else
		echo " * Error: Public key for asymmetric encryption could not be found"
		exit 6
	fi
fi

# Read the database configuration
echo " * Reading configuration of application ..."
APP_DEBUG=$(grep --perl-regexp --text --only-matching '(?<=APP_DEBUG=)(.+)' config/.env)
DB_DRIVER=$(grep --perl-regexp --text --only-matching '(?<=DB_DRIVER=)(.+)' config/.env)
DB_HOST=$(grep --perl-regexp --text --only-matching '(?<=DB_HOST=)(.+)' config/.env)
DB_PORT=$(grep --perl-regexp --text --only-matching '(?<=DB_PORT=)(.+)' config/.env)
DB_NAME=$(grep --perl-regexp --text --only-matching '(?<=DB_NAME=)(.+)' config/.env)
DB_USERNAME=$(grep --perl-regexp --text --only-matching '(?<=DB_USERNAME=)(.+)' config/.env)
DB_PASSWORD=$(grep --perl-regexp --text --only-matching '(?<=DB_PASSWORD=)(.+)' config/.env)
DB_CHARSET=$(grep --perl-regexp --text --only-matching '(?<=DB_CHARSET=)(.+)' config/.env)

# Verify that the configuration is complete
if [ "$DB_DRIVER" != "" ] && [ "$DB_HOST" != "" ] && [ "$DB_PORT" != "" ] && [ "$DB_NAME" != "" ] && [ "$DB_USERNAME" != "" ] && [ "$DB_CHARSET" != "" ]; then
	echo " * Configuration verified to be complete ..."
else
	echo " * Error: Configuration is incomplete"
	exit 7
fi

# Check whether the database driver specified in the configuration is supported
if [ "$DB_DRIVER" == "mysql" ]; then
	echo " * Database driver specified in the configuration is supported ..."
else
	echo " * Error: Database driver specified in the configuration is not supported"
	exit 8
fi

# Create a new directory for the current backup
echo " * Creating target directory for backup ..."
mkdir $BACKUP_DIRECTORY

# Create a temporary (and private) configuration file for MySQL to avoid passing the password on the command line
touch "${BACKUP_DIRECTORY}/${MYSQL_CONFIGURATION_FILENAME}"
chmod 0600 "${BACKUP_DIRECTORY}/${MYSQL_CONFIGURATION_FILENAME}"
echo -e "[mysqldump]\npassword=${DB_PASSWORD}" > "${BACKUP_DIRECTORY}/${MYSQL_CONFIGURATION_FILENAME}"

# Announce that the database export begins
echo " * Exporting the database ..."

# If the application is in debug mode
if [ "$APP_DEBUG" == "1" ]; then
	# Export the database schema
	echo "   * Exporting database schema ..."
	touch "${BACKUP_DIRECTORY}/${DATABASE_SCHEMA_FILENAME}"
	chmod 0600 "${BACKUP_DIRECTORY}/${DATABASE_SCHEMA_FILENAME}"
	mysqldump \
		--defaults-extra-file="${BACKUP_DIRECTORY}/${MYSQL_CONFIGURATION_FILENAME}" \
		--add-locks \
		--complete-insert \
		--create-options \
		--default-character-set="$DB_CHARSET" \
		--disable-keys \
		--skip-extended-insert \
		--lock-tables \
		--order-by-primary \
		--protocol=tcp \
		--quick \
		--quote-names \
		--set-charset \
		--skip-add-drop-table \
		--skip-comments \
		--skip-triggers \
		--tz-utc \
		--host="$DB_HOST" \
		--port="$DB_PORT" \
		--user="$DB_USERNAME" \
		--result-file="${BACKUP_DIRECTORY}/${DATABASE_SCHEMA_FILENAME}" \
		--no-data \
		"$DB_NAME"

	# Export the database seeds
	echo "   * Exporting database seeds ..."
	touch "${BACKUP_DIRECTORY}/${DATABASE_SEEDS_FILENAME}"
	chmod 0600 "${BACKUP_DIRECTORY}/${DATABASE_SEEDS_FILENAME}"
	mysqldump \
		--defaults-extra-file="${BACKUP_DIRECTORY}/${MYSQL_CONFIGURATION_FILENAME}" \
		--add-locks \
		--complete-insert \
		--create-options \
		--default-character-set="$DB_CHARSET" \
		--disable-keys \
		--skip-extended-insert \
		--lock-tables \
		--order-by-primary \
		--protocol=tcp \
		--quick \
		--quote-names \
		--set-charset \
		--skip-add-drop-table \
		--skip-comments \
		--skip-triggers \
		--tz-utc \
		--host="$DB_HOST" \
		--port="$DB_PORT" \
		--user="$DB_USERNAME" \
		--result-file="${BACKUP_DIRECTORY}/${DATABASE_SEEDS_FILENAME}" \
		--no-create-info \
		"$DB_NAME"
# If the application is in production mode
else
	# Export the entire database
	touch "${BACKUP_DIRECTORY}/${DATABASE_FULL_FILENAME}"
	chmod 0600 "${BACKUP_DIRECTORY}/${DATABASE_FULL_FILENAME}"
	mysqldump \
		--defaults-extra-file="${BACKUP_DIRECTORY}/${MYSQL_CONFIGURATION_FILENAME}" \
		--add-locks \
		--complete-insert \
		--create-options \
		--default-character-set="$DB_CHARSET" \
		--disable-keys \
		--extended-insert \
		--lock-tables \
		--order-by-primary \
		--protocol=tcp \
		--quick \
		--quote-names \
		--set-charset \
		--skip-add-drop-table \
		--skip-comments \
		--skip-triggers \
		--tz-utc \
		--host="$DB_HOST" \
		--port="$DB_PORT" \
		--user="$DB_USERNAME" \
		--result-file="${BACKUP_DIRECTORY}/${DATABASE_FULL_FILENAME}" \
		"$DB_NAME"
fi

# Delete the temporary (and private) configuration file for MySQL again
rm "${BACKUP_DIRECTORY}/${MYSQL_CONFIGURATION_FILENAME}"

# Export data from the file system
echo " * Exporting app storage and '.htaccess' ..."
touch "${BACKUP_DIRECTORY}/${FILE_SYSTEM_ARCHIVE_FILENAME}"
chmod 0600 "${BACKUP_DIRECTORY}/${FILE_SYSTEM_ARCHIVE_FILENAME}"
tar \
	--create \
	--file="${BACKUP_DIRECTORY}/${FILE_SYSTEM_ARCHIVE_FILENAME}" \
	".htaccess" \
	"storage/app"

# Export log files
echo " * Exporting log files ..."
touch "${BACKUP_DIRECTORY}/${LOGS_ARCHIVE_FILENAME}"
chmod 0600 "${BACKUP_DIRECTORY}/${LOGS_ARCHIVE_FILENAME}"
tar \
	--create \
	--file="${BACKUP_DIRECTORY}/${LOGS_ARCHIVE_FILENAME}" \
	$( \
		ls \
			-d \
			/var/log/apache2/access.log \
			/var/log/apache2/error.log \
			/var/log/apt/history.log \
			/var/log/apt/term.log \
			/var/log/auth.log \
			/var/log/cloud-init.log \
			/var/log/dpkg.log \
			/var/log/mysql/error.log \
			/var/log/ufw.log \
			/var/log/unattended-upgrades/unattended-upgrades.log \
			/var/log/unattended-upgrades/unattended-upgrades-dpkg.log \
			2>/dev/null \
	) \
	2>/dev/null

# Pack all exported data into a single archive
echo " * Packing exported data ..."
touch "${BACKUP_DIRECTORY}/${DATA_ARCHIVE_FILENAME}"
chmod 0600 "${BACKUP_DIRECTORY}/${DATA_ARCHIVE_FILENAME}"
tar \
	--create \
	--gzip \
	--file="${BACKUP_DIRECTORY}/${DATA_ARCHIVE_FILENAME}" \
	--directory="$BACKUP_DIRECTORY" \
	--exclude="$DATA_ARCHIVE_FILENAME" \
	.

# Delete the temporary files from the individual exports again
echo " * Deleting non-packed exports ..."
rm -f "${BACKUP_DIRECTORY}/${DATABASE_SCHEMA_FILENAME}"
rm -f "${BACKUP_DIRECTORY}/${DATABASE_SEEDS_FILENAME}"
rm -f "${BACKUP_DIRECTORY}/${DATABASE_FULL_FILENAME}"
rm -f "${BACKUP_DIRECTORY}/${FILE_SYSTEM_ARCHIVE_FILENAME}"
rm -f "${BACKUP_DIRECTORY}/${LOGS_ARCHIVE_FILENAME}"

if [ "$1" == "unencrypted" ]; then
	echo " * Skipping encryption as desired"
else
	# Generate random bytes to be used as the key for symmetric encryption
	echo " * Generating random key for symmetric encryption ..."
	touch "${BACKUP_DIRECTORY}/${SYMMETRIC_KEY_FILENAME}"
	chmod 0600 "${BACKUP_DIRECTORY}/${SYMMETRIC_KEY_FILENAME}"
	openssl rand -out "${BACKUP_DIRECTORY}/${SYMMETRIC_KEY_FILENAME}" 32

	# Symmetrically encrypt the exported data with the generated key
	echo " * Symmetrically encrypting exported data ..."
	touch "${BACKUP_DIRECTORY}/${DATA_ENCRYPTED_FILENAME}"
	chmod 0644 "${BACKUP_DIRECTORY}/${DATA_ENCRYPTED_FILENAME}"
	openssl enc \
		-aes-256-cbc \
		-e \
		-in "${BACKUP_DIRECTORY}/${DATA_ARCHIVE_FILENAME}" \
		-out "${BACKUP_DIRECTORY}/${DATA_ENCRYPTED_FILENAME}" \
		-pass "file:${BACKUP_DIRECTORY}/${SYMMETRIC_KEY_FILENAME}" \
		-salt

	# Delete the unencrypted version of the data again
	echo " * Deleting unencrypted data ..."
	rm -f "${BACKUP_DIRECTORY}/${DATA_ARCHIVE_FILENAME}"

	# Asymmetrically encrypt the symmetric encryption key
	echo " * Asymmetrically encrypting symmetric key ..."
	touch "${BACKUP_DIRECTORY}/${SYMMETRIC_KEY_ENCRYPTED_FILENAME}"
	chmod 0644 "${BACKUP_DIRECTORY}/${SYMMETRIC_KEY_ENCRYPTED_FILENAME}"
	openssl pkeyutl \
		-encrypt \
		-in "${BACKUP_DIRECTORY}/${SYMMETRIC_KEY_FILENAME}" \
		-out "${BACKUP_DIRECTORY}/${SYMMETRIC_KEY_ENCRYPTED_FILENAME}" \
		-inkey "${CONTAINER_DIRECTORY}/${ASYMMETRIC_PUBLIC_KEY_FILENAME}" \
		-keyform PEM \
		-pubin

	# Delete the unencrypted version of the symmetric encryption key again
	echo " * Deleting unencrypted symmetric key ..."
	rm -f "${BACKUP_DIRECTORY}/${SYMMETRIC_KEY_FILENAME}"

	# Add an exemplary script that shows how to decrypt the symmetric key and the data
	echo " * Adding manual for decryption"
	decryption="openssl pkeyutl -decrypt -in \"symmetric-key.bin.crypt\""
	decryption+=" -out \"symmetric-key.bin\" -inkey \"asymmetric-key.private.pem\""
	decryption+=" -keyform PEM"
	decryption+="\n"
	decryption+="openssl enc -aes-256-cbc -d -in \"data.tar.gz.crypt\""
	decryption+=" -out \"data.tar.gz\" -pass \"file:symmetric-key.bin\""
	echo -e $decryption > "${BACKUP_DIRECTORY}/${DECRYPTION_SAMPLE_SCRIPT_FILENAME}"
fi

# Pack the results into a single file
echo " * Packing results ..."
touch "${CONTAINER_DIRECTORY}/${BACKUP_FILENAME}"
chmod 0644 "${CONTAINER_DIRECTORY}/${BACKUP_FILENAME}"
tar \
	--create \
	--file="${CONTAINER_DIRECTORY}/${BACKUP_FILENAME}" \
	--directory="${BACKUP_DIRECTORY}" \
	.

# Remove the folder for the current backup that has now been packed
echo " * Deleting temporary directory ..."
rm -rf "$BACKUP_DIRECTORY"

# Announce that the backup process has finished
echo "Done"
