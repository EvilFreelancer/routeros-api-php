#!/bin/bash

ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=nos -p "$1" -l admin localhost "/user set admin password=admin; /quit;" -p "$1"
