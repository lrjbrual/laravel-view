<style>
body{
   background-color: #fff ;
   /*font-family: "Arial, Helvetica, sans-serif" !important;*/
   /*font-family:"Verdana" !important;*/
   font-family: Baskerville, "Baskerville Old Face", "Hoefler Text", Garamond, "Times New Roman", serif !important;

   font-size:14px;
   font-weight:200;
}
div {
    display: block;
        box-sizing: inherit;
}

.col-left {
    float: left;
    width: 50%;
}
.hr{
  border-bottom: 2px solid #c1c1c1;
  /*position: absolute;*/
  width:40%;
}

.col-right {
    margin-left:60px;
    float: left;
    width: 50%;
}

.p1{
  line-height: 150%;
  font-size:16px;
}

.paid{
  color:green;
  font-weight: 600;
}

.row {
  margin-top: 15px;
  /*margin: 0;*/
}

.row::after {
    content: "";
    display: table;
    clear: both;
}
.invoice-tbl{
    table-layout: fixed;
    width: 100%;
    max-width: 100%;
    margin-bottom: 1rem;
    display: table;
    border-collapse: separate;
}

.invoice-tbl th{
  font-size: 11px;
  padding:5px 0px 5px 0px;
  /*border:1px solid;*/
}
.invoice-tbl td{
  /*font-size: 16px;*/
}
/*.invoice-tbl thead{
  font-size: 11px;
}*/

.invoice-tbl th:nth-child(1),.invoice-tbl td:nth-child(1) {
    width: 60%;
}
.invoice-tbl th:nth-child(2),.invoice-tbl td:nth-child(2) {
    width: 10%;
}
.invoice-tbl th:nth-child(3),.invoice-tbl td:nth-child(3) {
    width: 15%;
}
.invoice-tbl th:nth-child(4),.invoice-tbl td:nth-child(4) {
    width: 17%;
}
.invoice-tbl > thead{
  border-top:solid 1px #c1c1c1;
  border-bottom:solid 1px #c1c1c1;
  /*display: table-header-group;
  horizontal-align: middle;
  border-color: inherit;*/
}

.invoice-tbl > thead > tr > th:nth-child(2),
.invoice-tbl > thead > tr > th:nth-child(3),
.invoice-tbl > thead > tr > th:nth-child(4),
.invoice-tbl > tbody > tr > td:nth-child(2),
.invoice-tbl > tbody > tr > td:nth-child(3),
.invoice-tbl > tbody > tr > td:nth-child(4){
  text-align: right;
}
.invoice-tbl > tbody{
  border-top:solid 1px #c1c1c1;
  border-bottom:solid 1px #c1c1c1;
}


.invoice-tbl td{
  padding: 5px 0px 5px 0px;
}

.invoice-tbl tbody tr:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.05);
}

.invoice-tbl tfoot td{
  text-align: right;
}
.invoice-tbl tfoot{
  border-bottom:solid 1px #c1c1c1;
}
</style>
