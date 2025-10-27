#!/bin/bash

PGUSER="user"
PGPASSWORD="password"
PGDATABASE="edu_platform"
PGHOST="db"

export PGPASSWORD

BACKUP_FILE="/backups/backup_$(date +%F_%H-%M).sql"

pg_dump -U $PGUSER -h $PGHOST $PGDATABASE > $BACKUP_FILE
echo "✅ Резервная копия создана: $BACKUP_FILE"