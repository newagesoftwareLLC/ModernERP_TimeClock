// IMPORTANT: Post to with JSON format data.
var express = require("express");
var app = express();
const cors = require('cors');
var mongodb = require('mongodb');
const url = require('url');
const fs = require("fs");

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

const PORT = process.env.PORT || 38001;

const MongoDB_URL = "mongodb://" + "localhost" + ":27017/"; // default mongoDB port is 27017

process.env['NODE_TLS_REJECT_UNAUTHORIZED'] = '0';

exports.express = express;
exports.cors = cors;
exports.app = app;
exports.mongodb = mongodb;
exports.MongoDB_URL = MongoDB_URL;

// retrieve employee list
app.get('/employees', cors(), (req,res) => {
	mongodb.MongoClient.connect(MongoDB_URL, function(err, db) {
		if (err) throw err;
		var dbo = db.db("internal-system");
		dbo.collection("employees").find({}).toArray(function(err, result) {
			if (err) throw err;
			console.log("retrieving employees list");
			res.send(result);
			db.close();
		});
	});
});

app.get('/punches', cors(), (req,res) => {
	mongodb.MongoClient.connect(MongoDB_URL, function(err, db) {
		if (err) throw err;
		var dbo = db.db("internal-system");
		dbo.collection("timeclock").find({ empid: req.body.empid }).toArray(function(err, result) {
			if (err) throw err;
			console.log("retrieving punches");
			res.send(result);
			db.close();
		});
	});
});

// punch data received from client
app.post('/punch', cors(), (req,res) => {
    console.log(req.body);
	if (typeof req.query.delete !== 'undefined') { // delete
		mongodb.MongoClient.connect(MongoDB_URL, function(err, db) {
			if (err) throw err;
			var dbo = db.db("internal-system");
				/*dbo.collection("employees").deleteOne({ _id: new mongodb.ObjectID(req.body._id) }, function(err, obj) {
					if (err) throw err;
					console.log("document " + req.body._id + " deleted");
					db.close();
					res.sendStatus(200);
				});*/
		});
	} else if (typeof req.query.edit !== 'undefined') { // edit (update)
		mongodb.MongoClient.connect(MongoDB_URL, function(err, db) {
			if (err) throw err;
			var dbo = db.db("internal-system");
				/*var report = {
					$set: {
						id: req.body.id,
						name: req.body.name,
						password: req.body.password
					}
				};
				dbo.collection("employees").updateOne({ _id: new mongodb.ObjectID(req.body._id) }, report, function(err, res2) {
					if (err) throw err;
					ws.send(JSON.stringify(report)); // may require ID?
					res.sendStatus(200);
					console.log("document " + req.body._id + " updated");
					db.close();
				});*/
		});
	} else { // insert new record
		mongodb.MongoClient.connect(MongoDB_URL, async function(err, db) {
				if (err) throw err;
				var dbo = db.db("internal-system");
				if(!err){
						var report = {
							empid: req.body.empid,
							datetime: new Date()
						};
						dbo.collection("timeclock").insertOne(report, function(err, res2) {
								if (err) throw err;
								//ws.send(JSON.stringify(report)); // may require ID?
								res.sendStatus(200);
								console.log("1 document inserted");
								db.close();
						});
				} else {
					console.log("err:" + err);
				}
		});
	}
});

const server = app.listen(PORT, function(){
	console.log("REST & Messaging Server Listening on port:" + server.address().port);
});