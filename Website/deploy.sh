#!/bin/bash

### PHP-Foundation (https://github.com/delight-im/PHP-Foundation)
### Copyright (c) delight.im (https://www.delight.im/)
### Licensed under the MIT License (https://opensource.org/licenses/MIT)

# Process command-line arguments

# If no command-line arguments (or only incomplete ones) have been provided
if [ "$1" == "" ] || [ "$2" == "" ] || [ "$3" == "" ] || [ "$4" == "" ]; then
	# Explain command
	echo "Usage:"
	echo "  ./deploy.sh {host} {port} {user} {path}"
	echo "    {host}: hostname of the target server, e.g. 'example.com'"
	echo "    {port}: SSH port at the target server, e.g. '22'"
	echo "    {user}: user for authentication at the target server, e.g. 'john-doe'"
	echo "    {path}: application directory on the target server (without trailing slash), e.g. '/var/www/example.com'"

	# If no command-line arguments have been specified at all
	if [ "$1" == "" ] && [ "$2" == "" ] && [ "$3" == "" ] && [ "$4" == "" ]; then
		# Return with success
		exit 0
	# If the command-line arguments have just been incomplete
	else
		# Return with failure
		exit 1
	fi
fi

# Define constants

# Set constants based on the command-line arguments received
TARGET_SSH_HOST=$1
TARGET_SSH_PORT=$2
TARGET_SSH_USER=$3
TARGET_APPLICATION_PATH=$4

# Generate a unique name for the new deployment
DEPLOYMENT_NAME="deployment-$(date -u +'%Y%m%dT%H%M%SZ')"

# Define the filename used for archives of the new deployment
DEPLOYMENT_ARCHIVE_FILENAME="$DEPLOYMENT_NAME.tar.gz"

# Start actual deployment

# Introduce the deployment to the user and confirm the target host and directory
echo "Deploying to '$TARGET_APPLICATION_PATH' on '$TARGET_SSH_HOST'"

# Verify that the source directory is a valid project root by looking for some important files and directories
if [ -d "app" ] && [ -f "index.php" ] && [ -d "public" ] && [ -d "vendor" ] && [ -d "views" ]; then
	echo " * Verified source directory ..."
else
	echo " * Source directory could not be verified ..."
	exit 2
fi

# Create an archive of all files in the source directory that are to be transferred to the target host
echo " * Packing files in source directory ..."
echo "   * Ignoring '.htaccess' file (environment-specific) ..." # [1]
echo "   * Ignoring 'config' directory (environment-specific) ..." # [1]
echo "   * Ignoring 'storage/app' directory (environment-specific) ..." # [1]
echo "   * Ignoring 'storage/framework' directory (environment-specific) ..." # [1]
touch "$DEPLOYMENT_ARCHIVE_FILENAME"
tar \
	--create \
	--gzip \
	--exclude "./$DEPLOYMENT_ARCHIVE_FILENAME" \
	--exclude "./.git" \
	--exclude "./.idea" \
	--exclude "./.htaccess" \
	--exclude "./config" \
	--exclude "./storage/app" \
	--exclude "./storage/framework" \
	--file="$DEPLOYMENT_ARCHIVE_FILENAME" \
	. # [1]

# Transfer the generated archive to the target host and delete it from the source directory afterwards
echo " * Moving packed files from source to target host ..."
scp -q -P "$TARGET_SSH_PORT" "$DEPLOYMENT_ARCHIVE_FILENAME" "${TARGET_SSH_USER}@${TARGET_SSH_HOST}:$TARGET_APPLICATION_PATH"
rm "$DEPLOYMENT_ARCHIVE_FILENAME"

# Establish an SSH connection to the target host
ssh -p "$TARGET_SSH_PORT" "${TARGET_SSH_USER}@${TARGET_SSH_HOST}" /bin/bash <<- EOF
	# Verify that the target directory exists, and, if found, switch to that directory
	if [ -d "$TARGET_APPLICATION_PATH" ]; then
		cd "$TARGET_APPLICATION_PATH"
	fi

	# Verify that the target directory is now the active working directory
	if [ "\$PWD" = "$TARGET_APPLICATION_PATH" ]; then
		echo " * Found target directory ..."
	else
		echo " * Target directory could not be found ..."
		exit 3
	fi

	# Verify that the target directory is a valid project root by looking for some important files and directories
	if [ -d "config" ] && [ -f ".htaccess" ] && [ -d "storage" ]; then
		echo " * Verified target directory ..."
	else
		echo " * Target directory could not be verified ..."
		exit 4
	fi

	# Enable maintenance mode on the site
	echo " * Enabling maintenance mode ..."
	sed -i 's/^\t# RewriteRule . maintenance.php \[END]/\tRewriteRule . maintenance.php [END]/m' .htaccess

	# Delete all directories and most files that new versions will subsequently be deployed for
	echo " * Cleaning up old files ..."
	find . \
		-depth \
		\! -path "./$DEPLOYMENT_ARCHIVE_FILENAME" \
		\! -path './index.php' \
		\! -path './maintenance.php' \
		\! -path './.htaccess' \
		\! -path './config' \
		\! -path './config/*' \
		\! -path './storage' \
		\! -path './storage/app' \
		\! -path './storage/app/*' \
		\! -path './storage/framework' \
		\! -path './storage/framework/*' \
		-delete # [1]

	# Extract the transferred archive in the target directory and delete the archive afterwards
	echo " * Unpacking files in target directory ..."
	tar --extract --gzip --overwrite --file="$DEPLOYMENT_ARCHIVE_FILENAME"
	rm "$DEPLOYMENT_ARCHIVE_FILENAME"

	# Disable maintenance mode on the site again
	echo " * Disabling maintenance mode ..."
	sed -i 's/^\tRewriteRule . maintenance.php \[END]/\t# RewriteRule . maintenance.php [END]/m' .htaccess

	# Announce that deployment has finished
	echo 'Done'
EOF

# [1] The entries in the set of ignored files should be kept consistent in all places
