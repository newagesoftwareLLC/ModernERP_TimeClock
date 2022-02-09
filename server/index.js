// IMPORTANT: Post to with JSON format data.
var express = require("express");
var WebSocket = require('ws');
var app = express();
const cors = require('cors');
var mongodb = require('mongodb');
//const https = require('https');
const url = require('url');
const fs = require("fs");

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

const PORT = process.env.PORT || 38001;

const MongoDB_URL = "mongodb://" + "localhost" + ":27017/"; // default mongoDB port is 27017
const WebSocket_URL = "ws://" + "localhost" + ":" + PORT;

process.env['NODE_TLS_REJECT_UNAUTHORIZED'] = '0';

exports.express = express;
exports.cors = cors;
exports.app = app;
exports.mongodb = mongodb;
exports.WebSocket = WebSocket;
exports.MongoDB_URL = MongoDB_URL;
exports.WebSocket_URL = WebSocket_URL;

// retrieve employee list
app.get('/employees', main.cors(), (req,res) => {
	main.mongodb.MongoClient.connect(main.MongoDB_URL, function(err, db) {
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

// punch data received from client
app.post('/punch', main.cors(), (req,res) => {
    console.log(req.body);
	if (typeof req.query.delete !== 'undefined') { // delete
		main.mongodb.MongoClient.connect(main.MongoDB_URL, function(err, db) {
			if (err) throw err;
			var dbo = db.db("internal-system");
				/*dbo.collection("employees").deleteOne({ _id: new main.mongodb.ObjectID(req.body._id) }, function(err, obj) {
					if (err) throw err;
					console.log("document " + req.body._id + " deleted");
					db.close();
					res.sendStatus(200);
				});*/
		});
	} else if (typeof req.query.edit !== 'undefined') { // edit (update)
		main.mongodb.MongoClient.connect(main.MongoDB_URL, function(err, db) {
			if (err) throw err;
			var dbo = db.db("internal-system");
				/*var report = {
					$set: {
						id: req.body.id,
						name: req.body.name,
						password: req.body.password
					}
				};
				dbo.collection("employees").updateOne({ _id: new main.mongodb.ObjectID(req.body._id) }, report, function(err, res2) {
					if (err) throw err;
					ws.send(JSON.stringify(report)); // may require ID?
					res.sendStatus(200);
					console.log("document " + req.body._id + " updated");
					db.close();
				});*/
		});
	} else { // insert new record
		main.mongodb.MongoClient.connect(main.MongoDB_URL, async function(err, db) {
				if (err) throw err;
				var dbo = db.db("internal-system");
				/*if(!err){
						var report = {
							id: req.body.id,
							name: req.body.name,
							password: req.body.password
						};
						dbo.collection("employees").insertOne(report, function(err, res2) {
								if (err) throw err;
								//ws.send(JSON.stringify(report)); // may require ID?
								res.sendStatus(200);
								console.log("1 document inserted");
								db.close();
						});
				} else {
					console.log("err:" + err);
				}*/
		});
    }
});