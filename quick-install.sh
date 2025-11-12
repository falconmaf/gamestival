#!/bin/bash
# Quick cleanup and fresh install
cd ~/gamestival
git reset --hard HEAD
git clean -fd
git pull origin main
chmod +x install-production.sh
./install-production.sh
