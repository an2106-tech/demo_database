const xlsx = require('xlsx');
const path = require('path');

const filePath = path.resolve('d:\\demo_database\\Bản sao của Workshop4.xlsx');
const workbook = xlsx.readFile(filePath);

console.log("Sheets:", workbook.SheetNames);
for (const sheetName of workbook.SheetNames) {
    console.log(`\n--- Sheet: ${sheetName} ---`);
    const worksheet = workbook.Sheets[sheetName];
    const data = xlsx.utils.sheet_to_json(worksheet, { header: 1 });
    // Print first 5 rows to see structure
    console.log(data.slice(0, 15));
}
