var Service = require('node-service-linux').Service;
 
var svc = new Service({
  name:'Intranet timeclock Server',
  description: 'Employee timeclock Service',
  script: '/path/to/timeclock/main.js'
});

svc.on('install',function(){
  svc.start();
});

svc.install();