<?php

$css_content = "
/*EXCEL STYLE*/
.report_area{
	background-color: white;
	font-size:13px;
	padding:5px;
	font-family: 'calibri', Arial, Trebuchet MS, tahoma, helvetica;
	width: 99%;
} 

.report_area table {
	margin-bottom:5px; 
	text-align: left; 
	font-size:13px;
	border-collapse: collapse;
}

.report_area table tr td{
	vertical-align: top;
}    
    
/*CONTENT --- HEADER*/
.xcenter{
	text-align:center; 
}
.xleft{
	text-align:left; 
}
.xright{
	text-align:right; 
}
.title_report {
	padding:0px;
	font-size: 20px;
	font-weight: bold;
}
.title_report_xcenter {
	padding:0px;
	font-size: 20px;
	font-weight: bold;
	text-align:center; 
}

.subtitle_report {
	padding:0px;
	font-size: 16px;
	font-weight: bold;
	text-align:left; 
}
.subtitle_report_xcenter {
	padding:0px;
	font-size: 16px;
	font-weight: bold;
	text-align:center; 
}

.logo img {
	padding-right: 10px;
}

.report_area_table_thead{
	background-color:#d8d8d8;
	border-top:1px solid #ccc;
}

.tbl_head_td{
	vertical-align: top; 
	text-align: left; 
	border-top:1px solid #ccc;
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	font-size:13px;
	line-height:14px;
	font-weight: bold;
	padding:5px;
	background-color:#d8d8d8;
}

.tbl_head_td_xcenter{
	vertical-align: top; 
	text-align: center; 
	border-top:1px solid #ccc;
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	font-size:13px;
	line-height:14px;
	font-weight: bold;
	padding:5px;
	background-color:#d8d8d8;
}

.tbl_head_td_xright{
	vertical-align: top; 
	text-align: right; 
	border-top:1px solid #ccc;
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	font-size:13px;
	line-height:14px;
	font-weight: bold;
	padding:5px;
	background-color:#d8d8d8;
}

.tbl_head_td_first{
	vertical-align: top; 
	text-align: left; 
	border-top:1px solid #ccc;
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	border-left:1px solid #ccc;
	font-size:13px;
	line-height:14px;
	font-weight: bold;
	padding:5px;
	background-color:#d8d8d8;
}

.tbl_head_td_first_xcenter{
	vertical-align: top; 
	text-align: center; 
	border-top:1px solid #ccc;
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	border-left:1px solid #ccc;
	font-size:13px;
	line-height:14px;
	font-weight: bold;
	padding:5px;
	background-color:#d8d8d8;
}
.tbl_head_td_first_xright{
	vertical-align: top; 
	text-align: right; 
	border-top:1px solid #ccc;
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	border-left:1px solid #ccc;
	font-size:13px;
	line-height:14px;
	font-weight: bold;
	padding:5px;
	background-color:#d8d8d8;
}

/*CONTENT --- DATA*/
.tbl_data_td{
	vertical-align: top; 
	text-align:left; 
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	padding:4px 5px;
	font-size:13px;
	line-height:16px;
}
.tbl_data_td_xcenter{
	vertical-align: top; 
	text-align:center; 
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	padding:4px 5px;
	font-size:13px;
	line-height:16px;
}
.tbl_data_td_xright{
	vertical-align: top; 
	text-align:right; 
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	padding:4px 5px;
	font-size:13px;
	line-height:16px;
}
.tbl_data_td_first{	
	vertical-align: top; 
	text-align:left; 
	border-left:1px solid #ccc;
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	padding:4px 5px;
	font-size:13px;
	line-height:16px;
}
.tbl_data_td_first_xcenter{	
	vertical-align: top; 
	text-align:center; 
	border-left:1px solid #ccc;
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	padding:4px 5px;
	font-size:13px;
	line-height:16px;
}
.tbl_data_td_first_xright{	
	vertical-align: top; 
	text-align:right; 
	border-left:1px solid #ccc;
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	padding:4px 5px;
	font-size:13px;
	line-height:16px;
}

/*CONTENT --- SUMMARY*/
.tbl_summary_td{
	vertical-align: top; 
	text-align:left; 
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	padding:4px 5px;
	font-weight:bold;
	font-size:14px;
	line-height:16px;
	background-color:#d8d8d8;
}
.tbl_summary_td_xcenter{	
	vertical-align: top; 
	text-align:center; 
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	padding:4px 5px;
	font-weight:bold;
	font-size:14px;
	line-height:16px;
	background-color:#d8d8d8;
}
.tbl_summary_td_xright{	
	vertical-align: top; 
	text-align:right; 
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	padding:4px 5px;
	font-weight:bold;
	font-size:14px;
	line-height:16px;
	background-color:#d8d8d8;
}

.tbl_summary_td_first{	
	vertical-align: top; 
	text-align:left; 
	border-left:1px solid #ccc;
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	padding:4px 5px;
	font-weight:bold;
	font-size:14px;
	line-height:16px;
	background-color:#d8d8d8;
}
.tbl_summary_td_first_xcenter{	
	vertical-align: top; 
	text-align:center; 
	border-left:1px solid #ccc;
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	padding:4px 5px;
	font-weight:bold;
	font-size:14px;
	line-height:16px;
	background-color:#d8d8d8;
}
.tbl_summary_td_first_xright{	
	vertical-align: top; 
	text-align:right; 
	border-left:1px solid #ccc;
	border-bottom:1px solid #ccc;
	border-right:1px solid #ccc;
	padding:4px 5px;
	font-weight:bold;
	font-size:14px;
	line-height:16px;
	background-color:#d8d8d8;
}


";

echo $css_content;