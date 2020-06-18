#!/usr/bin/env expect

set timeout 10

set port [lindex $argv 0]

spawn telnet localhost $port

expect "Login: "
send "admin+etc\n"
expect "Password: "
send "\n"
expect "]:"
send "n\r\n"
expect ">\ "
send "?\r\n"
expect ">\ "
send "\r\n"
expect ">\ "
send "/user set admin password=admin\r\n"
expect ">"
send "quit\r\n"
expect eof
