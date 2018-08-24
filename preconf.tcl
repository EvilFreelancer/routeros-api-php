#!/usr/bin/expect

set timeout 10

set port [lindex $argv 0]

spawn telnet localhost $port

expect "Login: "
send "admin+c\n"
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
send "quit\n"
expect eof
