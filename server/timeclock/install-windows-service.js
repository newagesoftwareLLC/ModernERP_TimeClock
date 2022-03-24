var Service = require('node-windows').Service;
var EventLogger = require('node-windows').EventLogger;

// Create a new service object
var svc = new Service({
  name:'Node timeclock Server',
  description: 'Node timeclock service script',
  script:'C:\\Program Files\\iisnode\\www\\timeclock\\main.js'
});

// Listen for the install event, which indicates the process is available as a service
svc.on('install',function(){
  svc.start();
  console.log("Service Installation Successful!");
});

var log = new EventLogger('node timeclock server');

console.log("About to start install. Check Event Viewer if not successful!");
svc.install();
