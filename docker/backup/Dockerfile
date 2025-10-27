FROM postgres:16

# Устанавливаем cron
RUN apt-get update && apt-get install -y cron bash

# Копируем скрипт бэкапа
COPY backup.sh /backups/backup.sh
RUN chmod +x /backups/backup.sh

# Копируем cron-файл
COPY backup-cron /etc/cron.d/backup-cron
RUN chmod 0644 /etc/cron.d/backup-cron

# Добавляем cron
RUN crontab /etc/cron.d/backup-cron

# Логи
RUN touch /var/log/cron.log

# Запуск cron в foreground
CMD cron && tail -f /var/log/cron.log
