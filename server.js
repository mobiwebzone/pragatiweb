const express = require('express');
const app = express();
const sql = require('mssql');
const bodyParser = require('body-parser');
var cors = require('cors');
const { createProxyMiddleware } = require('http-proxy-middleware');
app.use(cors());

// SQL Server database configuration
const config = {
  server: '78.46.65.247',
  database: 'MYEXAMPREP',
  user: 'sa',
  password: 'hPK1*2G$mwcztt3p@a$x9^94lc',
  // server: '192.168.10.6',
  // database: 'MYEXAMPREP',
  // user: 'sa',
  // password: 'sasa',
  options: {
    trustedConnection: true,
    encrypt: true,
    enableArithAbort: true,
    trustServerCertificate: true,

  },
};


// FOR FILE UPLOAD START
const multer = require('multer');
const path = require('path');
const fs = require('fs'); // Import the 'fs' module to work with the file system

const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, 'student_zone/images/homework/');
  },
  filename: (req, file, cb) => {
    // cb(null, Date.now() + path.extname(file.originalname));
    cb(null, file.originalname);
  },
});

const upload = multer({ storage });
// FOR FILE UPLOAD END

// Middleware
app.use(bodyParser.urlencoded({ extended: false }));
app.use(bodyParser.json());

// Connect to SQL Server
sql.connect(config)
  .then(() => console.log('Connected to SQL Server'))
  .catch((err) => console.log('Connection failed:', err));


//===========================================================================
//                              LOGIN START
//===========================================================================

// API endpoint for retrieving data from SQL Server
app.get('/api/login', async (req, res) => {
    try {
        const { username, password, logFor } = req.query;
        const customValue = req.query;
        const data = {};
        // console.log(username, password, logFor);
        const pool = await sql.connect(config);
        
        if(logFor==='ADMIN'){
          var query = `SELECT UID, FIRSTNAME, LASTNAME, USERROLE, (SELECT DBO.GET_CLEAR_USER_PASSWORD(UID))PWD,LOCID FROM USERS WHERE USERROLE IN ('ADMINISTRATOR','SUPERADMIN','USER') AND LOGINID='${username}'`;
        }else if(logFor==='TEACHER'){
          var query = `SELECT UID, FIRSTNAME, LASTNAME, USERROLE, (SELECT DBO.GET_CLEAR_USER_PASSWORD(UID))PWD,LOCID FROM USERS WHERE USERROLE IN ('TEACHER','VOLUNTEER') AND LOGINID='${username}'`;
        }else if(logFor==='STUDENT'){
          var query = `SELECT REGID UID, FIRSTNAME, LASTNAME, '' USERROLE, (SELECT DBO.GET_CLEAR_STUDENT_PASSWORD(REGID))PWD,LOCATIONID LOCID, APPROVED FROM REGISTRATIONS WHERE ARCHIVED=0 AND ISDELETED=0 AND LOGINID='${username}'`;
        }
        const result = await pool.request().query(query);
        // console.log(result.rowsAffected[0]);
        const rowAffected = result.rowsAffected.length>0 && result.rowsAffected[0]>0 ? true : false;
        if(rowAffected){
          if(result.recordset[0].PWD === password){
            if(result.recordset[0].APPROVED == 0){
              data['success'] = false;
              data['message'] = 'You can login once approved by MYEXAMSPREP.';
            }else{
              data['success'] = true;
              data['message'] = 'You are successfully logged In';
              data['data'] = result;
            }
          }else{
            data['success'] = false;
            data['message'] = 'Invalid userid or password.';
          }
          
          // data['pwd'] = result.recordset[0].PWD;
        }else{
          data['success'] = false;
          data['message'] = 'Invalid userid or password.';
          data['query'] = query;
        }
        // res.json(result.recordset);
        // console.log(JSON.stringify(data.data['recordsets']));
        res.json(data);
    } catch (error) {
        console.log('Error:', error);
        res.status(500).send('Internal Server Error');
    }
});

// GET LOCATIONS
app.get('/api/locations', async (req, res) => {
  try {
      const data = {};
      const pool = await sql.connect(config);
      const query = `SELECT LOC_ID,LOCATION FROM LOCATIONS WHERE ISDELETED=0 ORDER BY LOCATION`;
      const result = await pool.request().query(query);
      // console.log(result.recordset);
      const rowAffected = result.rowsAffected.length>0 && result.rowsAffected[0]>0 ? true : false;
      if(rowAffected){
          data['success'] = true;
          data['data'] = result.recordset;        
        // data['pwd'] = result.recordset[0].PWD;
      }else{
        data['success'] = false;
        data['message'] = 'Locations not found.';
        data['query'] = query;
      }
      // res.json(result.recordset);
      res.json(data);
  } catch (error) {
      console.log('Error:', error);
      res.status(500).send('Internal Server Error');
  }
});

// GET COUNTRIES
app.get('/api/contries', async (req, res) => {
  try {
      const data = {};
      const pool = await sql.connect(config);
      const query = `SELECT COUNTRYID,COUNTRY FROM COUNTRIES WHERE ISDELETED=0 ORDER BY COUNTRY`;
      const result = await pool.request().query(query);
      // console.log(result.recordset);
      const rowAffected = result.rowsAffected.length>0 && result.rowsAffected[0]>0 ? true : false;
      if(rowAffected){
          data['success'] = true;
          data['data'] = result.recordset;        
        // data['pwd'] = result.recordset[0].PWD;
      }else{
        data['success'] = false;
        data['message'] = 'Contries not found.';
        data['query'] = query;
      }
      // res.json(result.recordset);
      res.json(data);
  } catch (error) {
      console.log('Error:', error);
      res.status(500).send('Internal Server Error');
  }
});


// CREATE STUDENT ACCOUNT
app.post('/api/createStudentAccount', async (req, res) => {
  try {
      const data = {};
      const { location,mode,firstName,lastName,phone,email,grade,classof,school,address,city,state,zipcode,country,firstNameP1,lastNameP1,phoneP1,emailP1,firstNameP2,lastNameP2,phoneP2,emailP2,Addi_Instructions } = req.body;
      // console.log(firstName, lastName)
      // const formData = req.body;
      const porequest = await sql.connect(config);
      const query = `EXEC [REGISTRATIONS_SP] 1,0,${location},'${mode}',0,'${firstName}','${lastName}',
                    '${phone}','${email}','${grade}',${!classof || classof=='' ? 0 : classof},'${school}','${address}','',
                    '${city}','${state}','${zipcode}',${country},'','${firstNameP1}','${lastNameP1}',
                    '${phoneP1}','${emailP1}','${firstNameP2}','${lastNameP2}','${phoneP2}','${emailP2}',
                    '','','${Addi_Instructions}',1,0`;
      // console.log(query)
      const result = await porequest.request().query(query)
      .then((res) => {
        // console.log(res);
        data['success'] = true;
        data['message']='Account Successfully Created.';
      })
      .catch((error) => {
        data['success'] = false;
        console.error('Error inserting data:', error.originalError);
        data['message']=error.originalError.info.message;
      });
      res.json(data);
  } catch (error) {
      console.log('Error:', error);
      res.status(500).send('Internal Server Error');
  }
});


// CREATE TEACHER ACCOUNT
app.post('/api/createTeacherAccount', async (req, res) => {
  try {
      const data = {};
      console.log(req.body)
      const { firstName,lastName,phone,email,loginid,pass,location } = req.body;
      const formData = req.body;
      const porequest = await sql.connect(config);
      const query = `EXEC [USERS_SP] 1,0,'${firstName}','${lastName}','${phone}','${email}','${loginid}','${pass}',${location},'TEACHER',0,0`;
      // console.log('-------------------------------------');
      // console.log(query);
      // console.log('-------------------------------------');
      const result = await porequest.request().query(query)
      .then((res) => {
        console.log(res);
        data['success'] = true;
        data['message']='Account Successfully Created.';
      })
      .catch((error) => {
        data['success'] = false;
        console.error('Error inserting data:', error.originalError);
        data['message']=error.originalError.info.message;
      });
      res.json(data);
  } catch (error) {
      console.log('Error:', error);
      res.status(500).send('Internal Server Error');
  }
});

//===========================================================================
//                              LOGIN END
//===========================================================================




//===========================================================================
//                              DASHBOARD START
//===========================================================================
// GET LOCATIONS
app.get('/api/dashInfo', async (req, res) => {
  try {
      const { uid, locid } = req.query;
      const data = {};
      const pool = await sql.connect(config);

      // ##### ATTENDANCE
      const queryAtt = `SELECT TOP 1 SCCID,CONVERT(VARCHAR,CDATE,106)CDATE,
      (SELECT TITLE FROM INVENTORY WHERE INVID=SCC.INVID)INVENTORY,
      (SELECT CHAPTER FROM INV_CHAPTERS WHERE CHAPID=SCC.CHAPID)CHAPTER,REMARK
      FROM STUDENT_COURSE_COVERAGE SCC
      WHERE ISDELETED=0 AND
      SCCID IN (SELECT SCCID FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE ISDELETED=0 AND REGID=${uid})
      ORDER BY SCCID DESC`;
      const resultAtt = await pool.request().query(queryAtt);
      // console.log(result.recordset);
      const rowAffectedAtt = resultAtt.rowsAffected.length>0 && resultAtt.rowsAffected[0]>0 ? true : false;
      if(rowAffectedAtt){
          data['success_att'] = true;
          data['data_att'] = resultAtt.recordset;        
        // data['pwd'] = result.recordset[0].PWD;
      }else{
        data['success_att'] = false;
        data['message_att'] = 'Attendance data not found.';
        data['queryAtt'] = queryAtt;
      }


      // ##### ANNOUNCEMENT
      const queryAnn = `SELECT TOP 1 ANID,CONVERT(VARCHAR,ANDATE,106)ANDATE,ANNOUNCEMENT FROM ANNOUNCEMENTS A
      WHERE ISDELETED=0 AND (PLANID IN (SELECT PLANID FROM REGISTRATION_DETAILS WHERE REGID=${uid} AND CANCELLED=0 AND ACTIVATE=1) OR PLANID=0)
      AND (LOCID=${locid} OR LOCID=0)
      ORDER BY CONVERT(DATE,ANDATE,105) DESC`;
      const resultAnn = await pool.request().query(queryAnn);
      // console.log(result.recordset);
      const rowAffectedAnn = resultAnn.rowsAffected.length>0 && resultAnn.rowsAffected[0]>0 ? true : false;
      if(rowAffectedAnn){
          data['success_ann'] = true;
          data['data_ann'] = resultAnn.recordset;        
        // data['pwd'] = result.recordset[0].PWD;
      }else{
        data['success_ann'] = false;
        data['message_ann'] = 'Announcement data not found.';
        data['queryAnn'] = queryAnn;
      }


      // ##### MEETING LINKS
      const queryML = `SELECT MTID,MEETINGID,MEETINGLINK,MPASSCODE,
      (SELECT PLANNAME FROM PLANS WHERE PLANID=OML.PLANID)PLANNAME,
      (SELECT 
        CASE WHEN CONVERT(DATE,ENDDATE,105)>CONVERT(DATE,GETDATE(),105)
          THEN 'No'
          ELSE 'YES'
        END EXPIRE
      FROM PLANS WHERE PLANID=OML.PLANID)EXPIRE
      FROM ONLINE_MEETINGS_LINKS OML WHERE ISDELETED=0 
      AND PLANID IN (SELECT PLANID FROM REGISTRATION_DETAILS WHERE ISDELETED=0 AND REGID=${uid} AND CANCELLED=0 AND ACTIVATE=1)`;
      const resultML = await pool.request().query(queryML);
      // console.log(result.recordset);
      const rowAffectedML = resultML.rowsAffected.length>0 && resultML.rowsAffected[0]>0 ? true : false;
      if(rowAffectedML){
          data['success_ml'] = true;
          data['data_ml'] = resultML.recordset;        
        // data['pwd'] = result.recordset[0].PWD;
      }else{
        data['success_ml'] = false;
        data['message_ml'] = 'Meeting Links not found.';
        data['queryML'] = queryML;
      }


      // ##### RECEIPTS
      const queryRec = `SELECT TOP 1 RECID,CONVERT(VARCHAR,RECDATE,106)RECDATE,RECNO,RECNOFULL,AMOUNT,REFNO,REMARK,
      ISNULL((SELECT PLANNAME FROM PLANS WHERE PLANID=SR.PLANID),'No Plan')PLANNAME
      FROM STUDENT_RECEIPTS SR WHERE ISDELETED=0 AND REGID=${uid} ORDER BY RECID DESC`;
      const resultRec = await pool.request().query(queryRec);
      // console.log(result.recordset);
      const rowAffectedRec = resultRec.rowsAffected.length>0 && resultRec.rowsAffected[0]>0 ? true : false;
      if(rowAffectedRec){
          data['success_rec'] = true;
          data['data_rec'] = resultRec.recordset;        
        // data['pwd'] = result.recordset[0].PWD;
      }else{
        data['success_rec'] = false;
        data['message_rec'] = 'Receipts not found.';
        data['queryRec'] = queryRec;
      }

      res.json(data);
  } catch (error) {
      console.log('Error:', error);
      res.status(500).send('Internal Server Error');
  }
});
// CREATE STUDENT ACCOUNT
app.post('/api/saveRequest', async (req, res) => {
  try {
      const data = {};
      // console.log(req.body)
      const { uid,reason } = req.body;
      // console.log(uid,reason)
      const formData = req.body;
      const porequest = await sql.connect(config);
      const query = `INSERT INTO ACCOUNT_DELETE_REQUEST (USERTYPE,USERID,REASON)VALUES('STUDENT',${uid},'${reason}')`;
      // console.log('-------------------------------------');
      // console.log(query);
      // console.log('-------------------------------------');
      const result = await porequest.request().query(query)
      .then((res) => {
        console.log(res);
        data['success'] = true;
        data['message']='Your request has been submitted to myexamsprep for once the reuest accepted, you will get the notification.';
      })
      .catch((error) => {
        data['success'] = false;
        console.error('Error inserting data:', error.originalError);
        data['message']=error.originalError.info.message;
      });
      res.json(data);
  } catch (error) {
      console.log('Error:', error);
      res.status(500).send('Internal Server Error');
  }
});

//===========================================================================
//                              DASHBOARD END
//===========================================================================




//===========================================================================
//                              ATTENDANCE DATA START
//===========================================================================
app.get('/api/all_attendance_data', async (req, res) => {
  try {
      // console.log(req.query);
      const { uid } = req.query;
      const data = {};
      const pool = await sql.connect(config);

      const query = `SELECT SCCID,CONVERT(VARCHAR,CDATE,106)CDATE,
      (SELECT TITLE FROM INVENTORY WHERE INVID=SCC.INVID)INVENTORY,
      (SELECT CHAPTER FROM INV_CHAPTERS WHERE CHAPID=SCC.CHAPID)CHAPTER,REMARK
      FROM STUDENT_COURSE_COVERAGE SCC
      WHERE ISDELETED=0 AND
      SCCID IN (SELECT SCCID FROM STUDENT_COURSE_COVERAGE_ATTENDEDBY WHERE ISDELETED=0 AND REGID=${uid})
      ORDER BY CONVERT(DATE,CDATE,105) DESC,INVENTORY,CHAPTER`;
      const result = await pool.request().query(query);
      // console.log(result);
      const rowAffected = result.rowsAffected.length>0 && result.rowsAffected[0]>0 ? true : false;
      if(rowAffected){
          data['success'] = true;
          data['data'] = result.recordset;        
        // data['pwd'] = result.recordset[0].PWD;
      }else{
        data['success'] = false;
        data['message'] = 'Attendance data not found.';
        data['query'] = query;
      }

      res.json(data);
  } catch (error) {
      console.log('Error:', error);
      res.status(500).send('Internal Server Error');
  }
});

app.post('/api/saveHomework',upload.single('file'), async (req, res) => {
  try {
      console.log(req.file);
      const data = {};
      const {uid,SCCID,homework,homeworkDone,fileName,fileOldName} = req.body;
      const homeworkDoneF = homeworkDone=='false' ? 0 : 1;

      console.log('fileName:', fileName);
      console.log('fileOldName:', fileOldName);
      
    
      
      // const { uid } = req.query;
      const pool = await sql.connect(config);
      const checkFileInPost = (req.file == undefined || !req.file || !req.file.filename) ? '' : req.file.filename;
      const FinalFileName = (!fileName || fileName=='') ? fileOldName : fileName;
      const query = `UPDATE STUDENT_COURSE_COVERAGE_ATTENDEDBY SET HOMEWORK_DONE=${homeworkDoneF},STUDENTWORK='${homework}',HOMEWORK_IMG='${FinalFileName}' WHERE SCCID=${SCCID} AND REGID=${uid}`;
      const result = await pool.request().query(query)
      .then((res) => {
        // console.log('res : ',res);
        data['success'] = true;
        data['message']='Homework successfully submitted.';

        // UPLOAD FILE
        if (!checkFileInPost || checkFileInPost=='') {
          data['success_file'] = false;
          data['message_file'] ='No file uploaded.';
        }else{
  
          // Check if the uploaded file exists on the server
          const filePath = path.join(__dirname, 'student_zone/homework', req.file.filename);
          const oldFilePath = path.join(__dirname, 'student_zone/homework', fileOldName);
          fs.access(filePath, fs.constants.F_OK, (err) => {
            if (err) {
              data['success_file'] = false;
              // File does not exist
              // return res.status(404).send(data);
            }
            
            const saveFile = req.file;
            // console.log('length : ',Object.keys(saveFile).length);
            if(saveFile && Object.keys(saveFile).length>0){
              data['success_file'] = true;
              data['message_file'] ='File uploaded successfully.';
              data['uploadData'] = saveFile;

              // REMOVE OLD IMAGE
              if (fs.existsSync(oldFilePath)) {
                fs.unlinkSync(oldFilePath);
              }
            }else{
              data['success_file'] = false;
              data['message_file'] ='File uploading fail.';
            }
          });
        }
      })
      .catch((error) => {
        data['success'] = false;
        console.error('Error inserting data:', error.originalError);
        // data['message']=error.originalError.info.message;
        console.log(error)
        // data['message']=error.originalError.info.message;
      });
      
      res.send(data);
      // res.json(data);
  } catch (error) {
      console.log('Error:', error);
      res.status(500).send('Internal Server Error');
  }
});

//===========================================================================
//                              ATTENDANCE DATA END
//===========================================================================




//===========================================================================
//                              ANNOUNCEMENT DATA START
//===========================================================================
app.get('/api/all_announcement_data', async (req, res) => {
  try {
      // console.log(req.query);
      const { uid,locid } = req.query;
      const data = {};
      const pool = await sql.connect(config);

      const query = `SELECT ANID,CONVERT(VARCHAR,ANDATE,106)ANDATE,ANNOUNCEMENT FROM ANNOUNCEMENTS A
      WHERE ISDELETED=0 AND (PLANID IN (SELECT PLANID FROM REGISTRATION_DETAILS WHERE REGID=${uid} AND CANCELLED=0 AND ACTIVATE=1) OR PLANID=0)
      AND (LOCID=${locid} OR LOCID=0)
      ORDER BY CONVERT(DATE,ANDATE,105) DESC`;
      const result = await pool.request().query(query);
      // console.log(result);
      const rowAffected = result.rowsAffected.length>0 && result.rowsAffected[0]>0 ? true : false;
      if(rowAffected){
          data['success'] = true;
          data['data'] = result.recordset;        
        // data['pwd'] = result.recordset[0].PWD;
      }else{
        data['success'] = false;
        data['message'] = 'Announcement data not found.';
        data['query'] = query;
      }

      res.json(data);
  } catch (error) {
      console.log('Error:', error);
      res.status(500).send('Internal Server Error');
  }
});
//===========================================================================
//                              ANNOUNCEMENT DATA END
//===========================================================================




//===========================================================================
//                              RECEIPT DATA START
//===========================================================================
app.get('/api/all_receipt_data', async (req, res) => {
  try {
      // console.log(req.query);
      const { uid } = req.query;
      const data = {};
      const pool = await sql.connect(config);

      const query = `SELECT RECID,CONVERT(VARCHAR,RECDATE,106)RECDATE,RECNO,RECNOFULL,PLANID,
      ISNULL((SELECT PLANNAME FROM PLANS WHERE PLANID=SR.PLANID),'No Plan')PLANNAME,INSTALLMENT,AMOUNT,
      PMID,(SELECT PAYMENTMODE FROM PAYMENTMODES WHERE PMID=SR.PMID)PAYMODE,REFNO,
      (SELECT PAYPLAN FROM PAYMENT_SCHEDULE WHERE REGID=SR.REGID AND PLANID=SR.PLANID AND ISDELETED=0)PAYPLAN,
      REMARK
      FROM STUDENT_RECEIPTS SR WHERE ISDELETED=0 AND REGID=${uid}
      ORDER BY CONVERT(DATE,RECDATE,105) DESC`;
      const result = await pool.request().query(query);
      // console.log(result);
      const rowAffected = result.rowsAffected.length>0 && result.rowsAffected[0]>0 ? true : false;
      if(rowAffected){
          data['success'] = true;
          data['data'] = result.recordset;        
        // data['pwd'] = result.recordset[0].PWD;
      }else{
        data['success'] = false;
        data['message'] = 'Receipts not found.';
        data['query'] = query;
      }

      res.json(data);
  } catch (error) {
      console.log('Error:', error);
      res.status(500).send('Internal Server Error');
  }
});
//===========================================================================
//                              RECEIPT DATA END
//===========================================================================




//===========================================================================
//                              SMS DATA START
//===========================================================================
app.get('/api/all_sms_data', async (req, res) => {
  try {
      // console.log(req.query);
      const { uid } = req.query;
      const data = {};
      const pool = await sql.connect(config);

      const query = `SELECT MSGID,CONVERT(VARCHAR,MSGDATE,21)MSGDATE,MOBILENO,TEXTMESSAGE
      FROM TEXT_MESSAGES WHERE MSGTYPE='OUTGOING API' AND STUDENTTYPE='Registered' AND REGID=${uid} ORDER BY MSGID DESC`;
      const result = await pool.request().query(query);
      // console.log(result);
      const rowAffected = result.rowsAffected.length>0 && result.rowsAffected[0]>0 ? true : false;
      if(rowAffected){
          data['success'] = true;
          data['data'] = result.recordset;        
        // data['pwd'] = result.recordset[0].PWD;
      }else{
        data['success'] = false;
        data['message'] = 'Sms not found.';
        data['query'] = query;
      }

      res.json(data);
  } catch (error) {
      console.log('Error:', error);
      res.status(500).send('Internal Server Error');
  }
});
//===========================================================================
//                              SMS DATA END
//===========================================================================

// Proxy middleware to forward requests from React Native to this server
app.use('/proxy', createProxyMiddleware({
  target: 'http://localhost:3001', // Replace with your backend server URL
  changeOrigin: true,
  pathRewrite: {
    '^/proxy': '', // Remove /proxy prefix when forwarding to target
  },
}));

// Start the server
app.listen(3001, () => console.log('Server is running on port 3001'));
