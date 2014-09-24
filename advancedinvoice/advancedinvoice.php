<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");
require('includes/html_table.class.php');
function advancedinvoice_config() {
    $configarray = array(
    "name" => "Order and Invoice Managment",
    "description" => "Cancels invoices and orders that are greater than a defined time period",
    "version" => "1.0",
    "author" => "Patrick Hudson",
    "language" => "english",
    "fields" => array(
        "invoicedays" => array ("FriendlyName" => "Invoice Settings", "Type" => "text", "Size" => "25", "Description" => "Cancel Invoices older than", "Default" => "180", ),
        "orderdays" => array ("FriendlyName" => "Order Settings", "Type" => "text", "Size" => "25", "Description" => "Cancel orders older than", "Default" => "180", ),
        "billable" => array ("FriendlyName" => "Billable Items", "Type" => "yesno", "Size" => "25", "Description" => "Check this box to disable invoice cancelling for Billable items", "Default" => "0", ),
    ));
    return $configarray;
}

function advancedinvoice_activate() {

    # Return Result
    return array('status'=>'success','description'=>'This is an demo module only. In a real module you might instruct a user how to get started with it here...');
    return array('status'=>'error','description'=>'You can use the error status return to indicate there was a problem activating the module');
    return array('status'=>'info','description'=>'You can use the info status return to display a message to the user');

}

function advancedinvoice_deactivate() {

    # Return Result
    return array('status'=>'success','description'=>'If successful, you can return a message to show the user here');
    return array('status'=>'error','description'=>'If an error occurs you can return an error message for display here');
    return array('status'=>'info','description'=>'If you want to give an info message to a user you can return it here');

}

function advancedinvoice_upgrade($vars) {


}

function advancedinvoice_output($vars) {

    if ($_GET["view"] == "invoices"){
        advancedinvoice_showinvoices();
    }
function advancedinvoice_showinvoices(){
    $command = "getclients";
    $getclients = localAPI($command,$values);
    //echo '<pre>';
    //Get ALL invoices using http://docs.whmcs.com/API:Get_Invoices
    //this is needed to pull invoice IDs
    for ($invs = 0; $invs < $getclients['totalresults']; $invs++){
        $command = "getinvoices";
        $value['userid'] = $getclients['clients']['client'][$invs]['id'];
        //echo $value['userid']."searchforme";
        $getinvs = localAPI($command,$values);
        //echo count($getinvs[$inv]);=
    }
    //Specific Invoices 
    for($inv = 1; $inv < count($getinvs['invoices']['invoice']); $inv++){
        $command = "getinvoice";
        $values['invoiceid'] = $getinvs['invoices']['invoice'][$inv]['id'];
        $getinv[] = localAPI($command,$values);
          
    }
    //for use later.....
    //$config = advancedinvoice_config();
    //var_dump($config['fields']);
    $table = new HTML_Table('', 'datatable', array('width' => '100%', 'cellspacing' => '2', 'cellpadding' => "3"));
    $table->addRow();
    $table->addCell('Invoice Number', '', 'header', array('width' => '20px'));
    $table->addCell('Client', '', 'header');
    $table->addCell('Invoice Date', '', 'header');
    $table->addCell('Due Date', '', 'header');
    $table->addCell('Total Due', '', 'header');
    $table->addCell('Invoice Items', '', 'header');
    $table->addCell('Status', '', 'header');
    $table->addCell('edit', '', 'header');

    for($items = 0; $items < count($getinv); $items++){
        $d = date ( $format, strtotime ( '90 days' ) );
        if (strtotime($getinv[$items]['duedate']) > $d)
        {
            echo $getinv[$items]['invoiceid']. "is older than 90 days ";
        }
        if (count($getinv[$items]['items']['item']) > 1){
            for($item = 0; $item < count($getinv[$items]['items']['item']); $item++){
               if (empty($getinv[$items]['items']['item'][$item]['type']) && $type != 'Manual Line Item'){
                    $type .= "Manual Line Item";
               } 
               else{
                $count = count($getinv[$items]['items']['item']);
                    if(($count - 1) == $item) {
                        $type .= $getinv[$items]['items']['item'][$item]['type'];
                    }
                    else{
                        $type .= $getinv[$items]['items']['item'][$item]['type'] . " & ";
                    }
                
               }
            }
        }
        else {
            $type = $getinv[$items]['items']['item'][0]['type'];
        }        
            $linkopen = "<a href=\"invoices.php?action=edit&id=" . $getinv[$items]['invoiceid'] . "\">";
            $linkclose = "</a>";
            $table->addRow();

            //$table->addCell("<input type=\"checkbox\" name=\"selectedinvoices[]\" value=\"" . $getinv[$items]['invoiceid'] . "\" class=\"checkall\">");
            $table->addCell($linkopen . $getinv[$items]['invoiceid'] . $linkclose, 'clientname');
            $command = "getclientsdetails";
            $values["clientid"] = $getinv[$items]['userid'];
            $name = localAPI($command,$values);
            $table->addCell("<a href=\"clientssummary.php?userid=".$getinv[$items]['userid']."\">".$name['firstname']. " ". $name['lastname']);
            $table->addCell($getinv[$items]['date']);
            $table->addCell($getinv[$items]['duedate']);
            $table->addCell($getinv[$items]['subtotal']);
            if ($type == ""){
                $table->addCell('Manually created, without product');
            }
            elseif (count($type) > 1){
                var_dump($type);
                $table->addCell($type);
            }
            else{
                $table->addCell($type);
            }
            $table->addCell($getinv[$items]['status']);
            $table->addCell($linkopen . "<img src=\"images/edit.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"Edit\">");
            //$table->addCell();
    }
    echo $table->display();
    //var_dump($getinv);
    //echo 'Get Invoices Command'.PHP_EOL;
    //var_dump($getinv);
    //echo '</pre>';
}

function advancedinvoice_sidebar($vars) {


}
 function whmcsapi_xml_parser($rawxml) {
    $xml_parser = xml_parser_create();
    xml_parse_into_struct($xml_parser, $rawxml, $vals, $index);
    xml_parser_free($xml_parser);
    $params = array();
    $level = array();
    $alreadyused = array();
    $x=0;
    foreach ($vals as $xml_elem) {
      if ($xml_elem['type'] == 'open') {
         if (in_array($xml_elem['tag'],$alreadyused)) {
            $x++;
            $xml_elem['tag'] = $xml_elem['tag'].$x;
         }
         $level[$xml_elem['level']] = $xml_elem['tag'];
         $alreadyused[] = $xml_elem['tag'];
      }
      if ($xml_elem['type'] == 'complete') {
       $start_level = 1;
       $php_stmt = '$params';
       while($start_level < $xml_elem['level']) {
         $php_stmt .= '[$level['.$start_level.']]';
         $start_level++;
       }
       $php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';
       @eval($php_stmt);
      }
    }
    return($params);
 }