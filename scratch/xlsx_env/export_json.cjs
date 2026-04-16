const xlsx = require('xlsx');
const path = require('path');
const fs = require('fs');

const filePath = path.resolve('d:\\demo_database\\Bản sao của Workshop4.xlsx');
const workbook = xlsx.readFile(filePath);

const sheetName = 'Ws4_DailyMeeting';
const worksheet = workbook.Sheets[sheetName];
const data = xlsx.utils.sheet_to_json(worksheet, { header: 1 });

fs.writeFileSync(path.resolve('d:\\demo_database\\scratch\\xlsx_env\\daily_meeting.json'), JSON.stringify(data, null, 2));
