#!/bin/sh
set -eu
password_file=/run/rootrepair/.env.bak
if [ ! -r "$password_file" ]; then
    echo "SSH paroles avota fails nav pieejams" >&2
    exit 1
fi
password=$(sed -n 's/^JURIS_SSH_PASSWORD=//p' "$password_file")
if [ -z "$password" ]; then
    echo "SSH parole nav atrasta paroles avota failā" >&2
    exit 1
fi
printf 'juris:%s\n' "$password" | chpasswd
unset password
exec /usr/sbin/sshd -D -e
