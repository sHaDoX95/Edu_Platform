#!/bin/bash
PGPASSWORD=password pg_dump -U user -h db edu_platform > /backups/backup_$(date +%F_%H-%M).sql
