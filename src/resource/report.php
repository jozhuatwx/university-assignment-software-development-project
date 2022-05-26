<style>
body {
    font-family: 'Consolas', monospace;
}

p {
    margin: 0;
    line-height: 1.5rem;
}

table {
    width: 100%;
    margin-bottom: 30px;
}

table, th, tr, td {
    border: 1px solid black;
    border-collapse: collapse;
    padding: 5px;
    word-break: break-word;
}

.header p {
    text-align: center;
}

.content {
    margin: 15px 0;
}

.section-title {
    text-align: center;
    margin: 20px 0 10px 0;
}

.barchart-wrapper tr td {
    font-size: 1rem;
    vertical-align: top;
}

.report-barchart {
    -webkit-print-color-adjust: exact;
    width: 100%;
    height: calc(75vh - 150px);
    position: relative;
    margin-top: 30px
}

.report-barchart:before {
    content: "";
    width: 100%;
    height: 1px;
    background: #f2f2f2;
    position: absolute;
    top: 50%;
}

.report-barchart:after {
    content: "";
    width: 100%;
    height: 50%;
    background: transparent;
    position: absolute;
    top: 25%;
    border-top: 1px solid #f2f2f2;
    border-bottom: 1px solid #f2f2f2;
}

.report-bar {
    position: absolute;
    bottom: 0;
    z-index: 99;
    float: left;
}

.report-bar:before {
    position: absolute;
    width: 43px;
    text-align: center;
    top: -28px;
    font-size: 18px;
    left: calc(50% - 20px);
}

.legend-icon {
    -webkit-print-color-adjust: exact;
    height: 20px;
    width: 20px;
    display: inline-block;
    margin: 2.5px 0;
    vertical-align: center;
}

.legend-detail {    
    font-weight: 400;
    padding: 10px 0 0 15px;
    display: inline-block;
    width: 70%;
}
</style>