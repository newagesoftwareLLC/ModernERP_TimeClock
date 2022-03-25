#!/bin/bash

osascript -e 'if application "Terminal" is not running then tell application "Terminal" to activate'
osascript -e 'tell application "System Events" to tell process "Terminal" to keystroke "t" using command down'

osascript -e "tell application \"Terminal\" to do script \"cd ./client; npm install && npm start\" in window 1"
osascript -e "tell application \"Terminal\" to do script \"cd ./server/timeclock; npm install && npm start\" in window 2"