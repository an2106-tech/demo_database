const xlsx = require('xlsx');
const path = require('path');

const filePath = path.resolve('d:\\demo_database\\Bản sao của Workshop4.xlsx');
const workbook = xlsx.readFile(filePath);

const sheetName = workbook.SheetNames[0];
const worksheet = workbook.Sheets[sheetName];

const data = xlsx.utils.sheet_to_json(worksheet, { header: 1 });
console.log(JSON.stringify(data, null, 2));
