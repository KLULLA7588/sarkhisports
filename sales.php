<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
$conn = mysqli_connect("localhost", "root", "", "sarkhi sports1");
if (!$conn) { die("Database connection failed: " . mysqli_connect_error()); }
$db_name = 'sarkhi sports1';
$columns_to_add = [
    'size'=>"VARCHAR(100) NOT NULL DEFAULT ''",'pattern'=>"VARCHAR(100) NOT NULL DEFAULT ''",'fabric'=>"VARCHAR(100) NOT NULL DEFAULT ''",'color'=>"VARCHAR(100) NOT NULL DEFAULT ''",'delivery_date'=>"DATE NULL",'advance_date'=>"DATE NULL",'payment_mode'=>"VARCHAR(100) NOT NULL DEFAULT ''",'price'=>"DECIMAL(10,2) NOT NULL DEFAULT 0",'total'=>"DECIMAL(10,2) NOT NULL DEFAULT 0",'quantity'=>"INT NOT NULL DEFAULT 0",'discount_pct'=>"DECIMAL(5,2) NOT NULL DEFAULT 0",'discount_flat'=>"DECIMAL(10,2) NOT NULL DEFAULT 0",'extra_charges'=>"DECIMAL(10,2) NOT NULL DEFAULT 0",'print_charges'=>"DECIMAL(10,2) NOT NULL DEFAULT 0",'gross_amount'=>"DECIMAL(10,2) NOT NULL DEFAULT 0",'net_amount'=>"DECIMAL(10,2) NOT NULL DEFAULT 0",'remarks'=>"TEXT",'bill_type'=>"VARCHAR(50) NOT NULL DEFAULT 'Regular Bill'",'bill_status'=>"VARCHAR(50) NOT NULL DEFAULT 'Pending'",'size_rate_json'=>"TEXT",'all_items_json'=>"TEXT",
];
foreach ($columns_to_add as $col_name => $col_def) {
    $esc_db=mysqli_real_escape_string($conn,$db_name);$esc_col=mysqli_real_escape_string($conn,$col_name);
    $chk=mysqli_query($conn,"SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='$esc_db' AND TABLE_NAME='orders3' AND COLUMN_NAME='$esc_col' LIMIT 1");
    if($chk&&mysqli_num_rows($chk)===0){mysqli_query($conn,"ALTER TABLE orders3 ADD COLUMN `$col_name` $col_def");}
}
if(isset($_GET['action'])&&$_GET['action']==='generate_pdf'){
    ob_end_clean();ob_start();
    $tcpdf_paths=array(__DIR__.'/vendor/tecnickcom/tcpdf/tcpdf.php',__DIR__.'/tcpdf/tcpdf.php');
    $tcpdf_loaded=false;
    foreach($tcpdf_paths as $path){if(file_exists($path)){require_once $path;$tcpdf_loaded=true;break;}}
    if(!$tcpdf_loaded){ob_end_clean();header('Content-Type: application/json');echo json_encode(array('success'=>false,'error'=>'TCPDF not found'));exit;}
    $invoice_no=isset($_POST['invoice_no'])?trim($_POST['invoice_no']):'N/A';
    $customer_name=isset($_POST['customer_name'])?trim($_POST['customer_name']):'N/A';
    $order_date=isset($_POST['order_date'])?trim($_POST['order_date']):'';
    $delivery_date=isset($_POST['delivery_date'])?trim($_POST['delivery_date']):'';
    $advance_date=isset($_POST['advance_date'])?trim($_POST['advance_date']):'';
    $bill_type=isset($_POST['bill_type'])?trim($_POST['bill_type']):'';
    $bill_status=isset($_POST['bill_status'])?trim($_POST['bill_status']):'';
    $remarks=isset($_POST['remarks'])?trim($_POST['remarks']):'';
    $gross_amount=isset($_POST['gross_amount'])?floatval($_POST['gross_amount']):0;
    $disc_pct=isset($_POST['disc_pct'])?floatval($_POST['disc_pct']):0;
    $disc_flat=isset($_POST['disc_flat'])?floatval($_POST['disc_flat']):0;
    $extra_charges=isset($_POST['extra_charges'])?floatval($_POST['extra_charges']):0;
    $net_amount=isset($_POST['net_amount'])?floatval($_POST['net_amount']):0;
    $payment_modes=isset($_POST['payment_modes'])?trim($_POST['payment_modes']):'';
    $balance_due=isset($_POST['balance_due'])?floatval($_POST['balance_due']):0;
    $raw_items=isset($_POST['all_items_json'])?$_POST['all_items_json']:'[]';
    $items=json_decode(stripslashes($raw_items),true);
    if(!is_array($items))$items=array();
    $bills_dir=__DIR__.'/bills/';
    if(!is_dir($bills_dir))mkdir($bills_dir,0755,true);
    foreach(glob($bills_dir.'*.pdf') as $old){if(filemtime($old)<time()-7*86400)@unlink($old);}
    $safe_inv=preg_replace('/[^A-Za-z0-9\-_]/','_',$invoice_no);
    $filename='bill_'.$safe_inv.'_'.date('Ymd_His').'.pdf';
    $filepath=$bills_dir.$filename;
    function fmt_date($d){if(!$d)return '--';$ts=strtotime($d);return $ts?date('d M Y',$ts):$d;}
   $GLOBALS['pdf_invoice_no']=$invoice_no;
    class BillPDF extends TCPDF{
        public function Header(){
            $this->SetFillColor(255,255,255);
            $this->SetTextColor(0,0,0);
            $this->SetFont('helvetica','B',16);
            $this->SetXY(10,5);
            $this->Cell(130,8,'Sarthi Sports Wear',0,0,'L');
            $this->SetFont('helvetica','',8);
            $this->SetTextColor(0,0,0);
            $this->SetXY(10,14);
            $this->Cell(130,5,'Ph: 0762-0425141  |  Mob: 9422107750  |  Nagpur, Maharashtra',0,0,'L');
            $this->SetDrawColor(0,0,0);
            $this->SetLineWidth(0.6);
            $this->Line(10,22,200,22);
            $this->SetLineWidth(0.2);
        }
        public function Footer(){
            $this->SetY(-18);
            $this->SetDrawColor(0,0,0);
            $this->SetLineWidth(0.5);
            $this->Line(10,$this->GetY(),200,$this->GetY());
            $this->Ln(2);
            $this->SetFont('helvetica','',7);
            $this->SetTextColor(0,0,0);
            $this->Cell(130,5,'Thank you for your order! and visit again.',0,0,'L');
            $this->SetFont('helvetica','B',7);
            $this->Cell(60,5,'Authorised Signatory',0,0,'R');
        }
    }
    $pdf=new BillPDF('P','mm','A4',true,'UTF-8',false);
    $pdf->SetCreator('Sarthi Sports Wear');$pdf->SetAuthor('Sarthi Sports Wear');$pdf->SetTitle('Bill '.$invoice_no);
    $pdf->SetMargins(10,26,10);$pdf->SetHeaderMargin(0);$pdf->SetFooterMargin(20);
    $pdf->SetAutoPageBreak(true,24);$pdf->AddPage();
    $pdf->SetTextColor(0,0,0);$pdf->SetDrawColor(0,0,0);

    $info_y=$pdf->GetY()+2;
    $pdf->SetFillColor(240,240,240);
    $pdf->SetDrawColor(0,0,0);
    $pdf->Rect(10,$info_y,190,22,'FD');
    $pdf->SetFont('helvetica','B',7);$pdf->SetTextColor(0,0,0);
    $pdf->SetXY(13,$info_y+2);$pdf->Cell(60,4,'CUSTOMER NAME',0,0,'L');
    $pdf->SetFont('helvetica','B',11);$pdf->SetTextColor(0,0,0);
    $pdf->SetXY(13,$info_y+6);$pdf->Cell(60,6,strtoupper($customer_name),0,0,'L');
    $pdf->SetFont('helvetica','B',7);$pdf->SetTextColor(0,0,0);
    $pdf->SetXY(78,$info_y+2);$pdf->Cell(40,4,'ORDER DATE',0,0,'L');
    $pdf->SetFont('helvetica','',9);$pdf->SetTextColor(0,0,0);
    $pdf->SetXY(78,$info_y+7);$pdf->Cell(40,5,fmt_date($order_date),0,0,'L');
    $pdf->SetFont('helvetica','B',7);$pdf->SetTextColor(0,0,0);
    $pdf->SetXY(118,$info_y+2);$pdf->Cell(40,4,'DELIVERY DATE',0,0,'L');
    $pdf->SetFont('helvetica','',9);$pdf->SetTextColor(0,0,0);
    $pdf->SetXY(118,$info_y+7);$pdf->Cell(40,5,fmt_date($delivery_date),0,0,'L');
    $pdf->SetY($info_y+25);

    $col_w=array(8,28,22,18,20,14,16,11,18,10,25);
    $headers=array('#','PRODUCT','PATTERN','FABRIC','COLOR','SIZE','MRP','DIS%','NET RATE','QTY','AMOUNT');
    $aligns=array('C','L','L','L','L','C','R','C','R','C','R');
    $pdf->SetFillColor(0,0,0);$pdf->SetTextColor(255,255,255);
    $pdf->SetDrawColor(0,0,0);$pdf->SetFont('helvetica','B',7);$pdf->SetLineWidth(0.2);
    foreach($headers as $hi=>$h){$pdf->Cell($col_w[$hi],6,$h,1,0,$aligns[$hi],true);}$pdf->Ln();

    $row_n=0;$grand_qty=0;$grand_gross=0;
    foreach($items as $item){
        $product=ucwords(str_replace('_',' ',isset($item['product'])?$item['product']:''));
        $pattern=isset($item['pattern'])?$item['pattern']:'';
        $fabric=isset($item['fabric'])?$item['fabric']:'';
        $color=isset($item['color'])?$item['color']:'';
        $sizes=isset($item['sizes'])?$item['sizes']:array();
        $print_chg=floatval(isset($item['print_charges'])?$item['print_charges']:0);
        $first=true;$item_qty=0;$item_amt=0;
        foreach($sizes as $sz){
            $qty=floatval(isset($sz['qty'])?$sz['qty']:0);
            if($qty<=0)continue;
            $mrp=floatval(isset($sz['mrp'])?$sz['mrp']:0);
            $nr=floatval(isset($sz['netrate'])?$sz['netrate']:0);
            $disc_p=($mrp>0&&$nr>0)?round(($mrp-$nr)/$mrp*100,0):0;
            $amt=floatval(isset($sz['amount'])?$sz['amount']:0);
            $sz_label=isset($sz['size'])?$sz['size']:'';
            $item_qty+=$qty;$item_amt+=$amt;
            if($row_n%2===0){$pdf->SetFillColor(255,255,255);}else{$pdf->SetFillColor(240,240,240);}
            $pdf->SetTextColor(0,0,0);$pdf->SetDrawColor(136,136,136);$pdf->SetFont('helvetica','',7);
            $y0=$pdf->GetY();
            if($y0>255){$pdf->AddPage();$pdf->SetFillColor(0,0,0);$pdf->SetTextColor(255,255,255);$pdf->SetDrawColor(0,0,0);$pdf->SetFont('helvetica','B',7);foreach($headers as $hi=>$h){$pdf->Cell($col_w[$hi],6,$h,1,0,$aligns[$hi],true);}$pdf->Ln();$y0=$pdf->GetY();}
            $cells=array(
                $first?($row_n+1):'',
                $first?$product:'',
                $pattern,$fabric,$color,$sz_label,
                $mrp?'Rs.'.number_format($mrp,0):'',
                $disc_p?$disc_p.'%':'',
                $nr?'Rs.'.number_format($nr,0):'',
                intval($qty),
                'Rs.'.number_format($amt,0)
            );
            $x0=10;
            foreach($cells as $ci=>$cv){
                $pdf->SetXY($x0+array_sum(array_slice($col_w,0,$ci)),$y0);
                $pdf->Cell($col_w[$ci],6,$cv,1,0,$aligns[$ci],true);
            }
            $pdf->Ln(6);$first=false;$row_n++;
        }
        $grand_qty+=$item_qty;$grand_gross+=$item_amt;
    }

    $pdf->SetFillColor(0,0,0);$pdf->SetTextColor(255,255,255);$pdf->SetFont('helvetica','B',8);
    $total_label_w=array_sum($col_w)-$col_w[9]-$col_w[10];
    $pdf->Cell($total_label_w,7,'TOTAL QTY & GROSS',1,0,'R',true);
    $pdf->Cell($col_w[9],7,$grand_qty,1,0,'C',true);
    $pdf->Cell($col_w[10],7,'Rs.'.number_format($grand_gross,0),1,0,'R',true);
    $pdf->Ln(10);

    $y_bot=$pdf->GetY();
    $disc_calc=$gross_amount*$disc_pct/100;
    $grand_print=0;
foreach($items as $item){ $grand_print+=floatval(isset($item['print_charges'])?$item['print_charges']:0); }
    $t_rows=array();
    $t_rows[]=array('Gross Amount','Rs. '.number_format($gross_amount,2),false);
    if($grand_print>0)$t_rows[]=array('Print Charges','Rs. '.number_format($grand_print,2),false);
    if($disc_pct>0)$t_rows[]=array('Discount ('.$disc_pct.'%)','- Rs. '.number_format($disc_calc,2),false);
    if($disc_flat>0)$t_rows[]=array('Discount Flat','- Rs. '.number_format($disc_flat,2),false);
    if($extra_charges>0)$t_rows[]=array('GST / Charges','Rs. '.number_format($extra_charges,2),false);
    $t_rows[]=array('NET AMOUNT','Rs. '.number_format($net_amount,2),true);
    $left_h=count($t_rows)*7+2;
    $pdf->SetDrawColor(0,0,0);$pdf->SetLineWidth(0.2);
    $pdf->Rect(10,$y_bot,90,$left_h,'D');
    $ty=$y_bot+1;
    foreach($t_rows as $tr){
        if($tr[2]){
            $pdf->SetFillColor(0,0,0);$pdf->Rect(10,$ty,90,8,'F');
            $pdf->SetFont('helvetica','B',9);$pdf->SetTextColor(255,255,255);
            $pdf->SetXY(13,$ty+1);$pdf->Cell(44,6,$tr[0],0,0,'L');
            $pdf->SetXY(13,$ty+1);$pdf->Cell(84,6,$tr[1],0,0,'R');
            $ty+=8;
        }else{
            $pdf->SetFont('helvetica','',8);$pdf->SetTextColor(0,0,0);
            $pdf->SetXY(13,$ty+1);$pdf->Cell(44,5,$tr[0],0,0,'L');
            $pdf->SetXY(13,$ty+1);$pdf->Cell(84,5,$tr[1],0,0,'R');
            $pdf->SetDrawColor(200,200,200);$pdf->Line(10,$ty+6,100,$ty+6);
            $pdf->SetDrawColor(0,0,0);
            $ty+=7;
        }
    }
    $pdf->SetDrawColor(0,0,0);
    $pdf->Rect(105,$y_bot,95,$left_h,'D');
    $pdf->SetFont('helvetica','B',8);$pdf->SetTextColor(0,0,0);
    $pdf->SetXY(108,$y_bot+2);$pdf->Cell(88,5,'PAYMENT DETAILS',0,0,'L');
    $pdf->SetDrawColor(0,0,0);$pdf->Line(105,$y_bot+8,200,$y_bot+8);
    $pdf->SetFont('helvetica','',8);
    $py=$y_bot+10;
    if($payment_modes){foreach(explode(',',$payment_modes) as $pm){$pm=trim($pm);if($pm){$pdf->SetXY(108,$py);$pdf->Cell(88,5,$pm,0,1,'L');$py+=5;}}}

 if($remarks){
        $pdf->SetY(max($pdf->GetY(),$y_bot+$left_h+3));
        $pdf->SetFillColor(240,240,240);$pdf->SetDrawColor(0,0,0);
        $pdf->SetFont('helvetica','B',8);$pdf->SetTextColor(0,0,0);
        $pdf->Cell(190,6,'  Remarks: '.$remarks,1,1,'L',true);
    }

    try { $pdf->Output($filepath,'F'); }
    catch(Exception $e){$buffered=ob_get_clean();header('Content-Type: application/json');echo json_encode(array('success'=>false,'error'=>'PDF output error: '.$e->getMessage()));exit;}
    $buffered=ob_get_clean();
    if(!empty($buffered)){header('Content-Type: application/json');echo json_encode(array('success'=>false,'error'=>'PHP output before JSON: '.substr($buffered,0,500)));exit;}
    header('Content-Type: application/octet-stream');
    header('Content-Transfer-Encoding: binary');
    header('Content-Disposition: attachment; filename="Bill_'.$safe_inv.'.pdf"');
    header('Content-Length: '.filesize($filepath));
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    readfile($filepath);
    exit;
}
$success_msg='';$error_msg='';
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $customer_name=mysqli_real_escape_string($conn,trim(isset($_POST['customer_name'])?$_POST['customer_name']:''));$invoice_no=mysqli_real_escape_string($conn,trim(isset($_POST['invoice_no'])?$_POST['invoice_no']:''));$order_date=mysqli_real_escape_string($conn,isset($_POST['order_date'])?$_POST['order_date']:date('Y-m-d'));$delivery_date=mysqli_real_escape_string($conn,isset($_POST['delivery_date'])?$_POST['delivery_date']:'');$advance_date=mysqli_real_escape_string($conn,isset($_POST['advance_date'])?$_POST['advance_date']:'');$bill_type=mysqli_real_escape_string($conn,isset($_POST['bill_type'])?$_POST['bill_type']:'Regular Bill');$bill_status=mysqli_real_escape_string($conn,isset($_POST['bill_status'])?$_POST['bill_status']:'Pending');$remarks=mysqli_real_escape_string($conn,trim(isset($_POST['remarks'])?$_POST['remarks']:''));$disc_pct=floatval(isset($_POST['disc_pct'])?$_POST['disc_pct']:0);$disc_flat=floatval(isset($_POST['disc_flat'])?$_POST['disc_flat']:0);$extra_charges=floatval(isset($_POST['extra_charges'])?$_POST['extra_charges']:0);$gross_amount=floatval(isset($_POST['gross_amount'])?$_POST['gross_amount']:0);$net_amount=floatval(isset($_POST['net_amount'])?$_POST['net_amount']:0);$all_items_json=mysqli_real_escape_string($conn,isset($_POST['all_items_json'])?$_POST['all_items_json']:'');
    $raw_json=isset($_POST['all_items_json'])?$_POST['all_items_json']:'[]';$items=json_decode(stripslashes($raw_json),true);if(!is_array($items))$items=array();
    $payment_modes_raw=isset($_POST['payment_modes'])?$_POST['payment_modes']:'';$payment_mode_str=mysqli_real_escape_string($conn,$payment_modes_raw);
    $insert_count=0;$errors=array();
    foreach($items as $idx=>$item){$product=mysqli_real_escape_string($conn,isset($item['product'])?$item['product']:'');$pattern=mysqli_real_escape_string($conn,isset($item['pattern'])?$item['pattern']:'');$fabric=mysqli_real_escape_string($conn,isset($item['fabric'])?$item['fabric']:'');$color=mysqli_real_escape_string($conn,isset($item['color'])?$item['color']:'');$notes=mysqli_real_escape_string($conn,isset($item['notes'])?$item['notes']:'');$size_json=mysqli_real_escape_string($conn,json_encode(isset($item['sizes'])?$item['sizes']:array()));$print_chg=floatval(isset($item['print_charges'])?$item['print_charges']:0);if(!$product)continue;$item_qty=0;$item_gross=0;$first_price=0;$first_size='';$item_sizes=isset($item['sizes'])?$item['sizes']:array();$size_loop_i=0;foreach($item_sizes as $si=>$sz){$qty=floatval(isset($sz['qty'])?$sz['qty']:0);$amt=floatval(isset($sz['amount'])?$sz['amount']:0);$item_qty+=$qty;$item_gross+=$amt;if($size_loop_i===0){$first_price=floatval(isset($sz['mrp'])?$sz['mrp']:0);$first_size=isset($sz['size'])?$sz['size']:'';}$size_loop_i++;}
    $del=($delivery_date!=='')?("'$delivery_date'"):"NULL";$adv=($advance_date!=='')?("'$advance_date'"):"NULL";
    $sql="INSERT INTO orders3 (invoice_no,customer_name,order_date,delivery_date,advance_date,product,size,pattern,fabric,color,quantity,price,total,gross_amount,net_amount,payment_mode,discount_pct,discount_flat,extra_charges,print_charges,bill_type,bill_status,remarks,size_rate_json,all_items_json) VALUES ('$invoice_no','$customer_name','$order_date',$del,$adv,'$product','".mysqli_real_escape_string($conn,$first_size)."','$pattern','$fabric','$color','$item_qty','$first_price','".number_format($item_gross,2,'.','')."','".number_format($item_gross,2,'.','')."','".number_format($net_amount,2,'.','')."','$payment_mode_str','$disc_pct','$disc_flat','$extra_charges','$print_chg','$bill_type','$bill_status','$remarks','$size_json','$all_items_json')";
    if(mysqli_query($conn,$sql)){$insert_count++;}else{$errors[]="Row ".($idx+1).": ".mysqli_error($conn);}
    }
    if($insert_count>0&&empty($errors)){$success_msg="Order saved! $insert_count item(s) saved for Invoice $invoice_no.";}elseif(!empty($errors)){$error_msg=implode('<br>',$errors);}else{$error_msg="No items were saved.";}
}
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sarthi Sports Wear - Order Form</title>
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --bg:#1b2230;--panel:#1e2a3a;--panel2:#243040;--border:#2e4060;--border-light:#3a5070;
    --accent:#00aaff;--accent2:#00d4aa;--text:#ffffff;--text-dim:#c0d8f0;--text-label:#8ab8d8;
    --input-bg:#111c28;--row-even:#1a2535;--row-odd:#1e2d40;--header-bg:#162030;
    --btn-green:#00c47a;--btn-blue:#0088ee;--btn-red:#e04040;--btn-gray:#445566;--highlight:#004a88;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Barlow',sans-serif;background:#0f1720;min-height:100vh;padding:22px;color:var(--text);font-size:20px;}
.container{max-width:1900px;margin:0 auto;}
.topbar{background:var(--header-bg);border:1px solid var(--border);border-bottom:2px solid var(--accent);border-radius:6px 6px 0 0;padding:10px 26px;display:flex;align-items:center;justify-content:space-between;font-size:18px;color:var(--text-dim);font-family:'Share Tech Mono',monospace;}
.topbar .brand{color:var(--accent);font-size:21px;font-weight:bold;letter-spacing:1px;}
.topbar .info-pills{display:flex;gap:16px;align-items:center;}
.topbar .pill{background:var(--panel2);padding:4px 14px;border-radius:3px;border:1px solid var(--border);font-size:17px;}
.topbar .pill span{color:var(--accent2);}
.banner{padding:16px 26px;font-size:20px;font-weight:600;border:1px solid;margin-bottom:0;}
.banner.success{background:rgba(0,196,122,0.12);border-color:var(--btn-green);color:var(--btn-green);}
.banner.error{background:rgba(224,64,64,0.12);border-color:var(--btn-red);color:#ff8080;}
.header-panel{background:var(--panel);border:1px solid var(--border);border-top:none;padding:16px 24px;display:grid;grid-template-columns:repeat(7,1fr);gap:10px;align-items:end;}
.header-panel .shop-name-block{grid-column:span 2;}
.shop-name-block h1{font-size:32px;font-weight:700;color:var(--accent);letter-spacing:0.5px;line-height:1.1;}
.shop-name-block p{font-size:17px;color:var(--text-dim);margin-top:3px;}
.hfield{display:flex;flex-direction:column;gap:3px;}
.hfield label{font-size:16px;text-transform:uppercase;letter-spacing:0.5px;color:#ffffff !important;font-weight:600;}
.hfield input,.hfield select{background:var(--input-bg);border:1px solid var(--border);border-radius:3px;color:#ffffff !important;font-size:20px;font-family:'Share Tech Mono',monospace;padding:8px 12px;width:100%;transition:border-color 0.2s;}
.hfield input:focus,.hfield select:focus{outline:none;border-color:var(--accent);background:#0d1520;}
.hfield input[readonly]{color:#ffffff !important;}
.hfield input[type="date"]{color:#ffffff !important;color-scheme:dark;}
.invoice-badge{background:var(--highlight);border:1px solid var(--accent);border-radius:3px;padding:7px 12px;font-family:'Share Tech Mono',monospace;font-size:28px;color:#ffffff !important;font-weight:bold;text-align:center;}
.tabs{background:var(--header-bg);border:1px solid var(--border);border-top:none;display:flex;gap:2px;padding:5px 10px 0;}
.tab{padding:11px 32px;font-size:19px;font-weight:600;border-radius:4px 4px 0 0;cursor:pointer;border:1px solid transparent;border-bottom:none;color:var(--text-dim);background:transparent;letter-spacing:0.3px;}
.tab.active{background:var(--panel);border-color:var(--border);color:var(--accent);}
.grid-area{background:var(--panel);border:1px solid var(--border);border-top:none;overflow-x:auto;}
.grid-table{width:100%;border-collapse:collapse;font-size:20px;min-width:960px;}
.grid-table thead tr{background:var(--header-bg);}
.grid-table thead th{padding:11px 13px;text-align:center;font-size:17px;font-weight:700;text-transform:uppercase;letter-spacing:0.4px;color:#ffffff;border-right:1px solid var(--border);border-bottom:2px solid var(--border-light);white-space:nowrap;}
.grid-table thead th:first-child{text-align:left;padding-left:14px;}
.grid-table tbody tr{border-bottom:1px solid var(--border);}
.grid-table tbody tr:nth-child(even){background:var(--row-even);}
.grid-table tbody tr:nth-child(odd){background:var(--row-odd);}
.grid-table tbody tr.active-row{background:#0a2a4a!important;outline:1px solid var(--accent);}
.grid-table tbody td{padding:5px 5px;border-right:1px solid var(--border);vertical-align:middle;}
.grid-table tbody td:first-child{width:38px;text-align:center;color:#ffffff;font-family:'Share Tech Mono',monospace;font-size:17px;}
.grid-table tbody td input,.grid-table tbody td select{width:100%;background:transparent;border:none;color:var(--text);font-size:20px;font-family:'Barlow',sans-serif;padding:5px 8px;}
.grid-table tbody td input:focus,.grid-table tbody td select:focus{outline:none;background:var(--input-bg);border-radius:2px;}
.size-rate-panel{display:none;background:#0d1a28;border:1px solid var(--border-light);border-radius:4px;padding:0;min-width:640px;overflow:hidden;}
.size-rate-panel.visible{display:block;}
.sr-pattern-sel,.sr-fabric-inp,.sr-color-sel{background:transparent;border:none;color:var(--text);font-size:19px;font-family:'Barlow',sans-serif;padding:3px 5px;width:100%;}
.sr-pattern-sel:focus,.sr-fabric-inp:focus,.sr-color-sel:focus{outline:none;background:var(--input-bg);border-radius:2px;}
.size-rate-panel table{width:100%;border-collapse:collapse;font-size:19px;}
.size-rate-panel th{background:var(--header-bg);color:#ffffff;font-size:16px;text-transform:uppercase;padding:4px 6px;border:1px solid var(--border);}
.size-rate-panel td{border:1px solid var(--border);padding:2px;}
.size-rate-panel input{background:transparent;border:none;color:var(--text);font-size:19px;font-family:'Share Tech Mono',monospace;padding:3px 5px;width:100%;}
.size-rate-panel input:focus{outline:none;background:var(--input-bg);border-radius:2px;}
.rm-size-btn{background:transparent;border:none;color:var(--btn-red);cursor:pointer;font-size:19px;padding:0 4px;}
.rm-size-btn:hover{color:#ff6060;}
.add-size-btn{background:transparent;border:1px dashed var(--border-light);color:var(--accent);font-size:18px;font-weight:600;padding:4px 10px;cursor:pointer;border-radius:0;margin:0;width:100%;text-align:center;display:block;}
.add-size-btn:hover{background:rgba(0,170,255,0.08);}
.footer-area{background:var(--panel);border:1px solid var(--border);border-top:2px solid var(--border-light);padding:16px 24px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;align-items:start;}
.summary-col{display:flex;flex-direction:column;gap:10px;}
.sfield{display:flex;flex-direction:column;gap:3px;}
.sfield label{font-size:16px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-label);font-weight:600;}
.sfield input{background:var(--input-bg);border:1px solid var(--border);border-radius:3px;color:var(--text);font-size:19px;font-family:'Share Tech Mono',monospace;padding:6px 10px;width:100%;}
.sfield input:focus{outline:none;border-color:var(--accent);}
.qty-items-row{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.pc-footer-block{background:var(--input-bg);border:1px solid var(--border);border-radius:3px;padding:10px 12px;display:flex;flex-direction:column;gap:8px;}
.pc-footer-title{font-size:17px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-label);display:flex;align-items:center;gap:6px;}
.pc-footer-title::after{content:'';flex:1;height:1px;background:var(--border);}
.pc-footer-inputs{display:grid;grid-template-columns:auto 1fr auto 1fr;gap:6px 10px;align-items:center;}
.pc-inp-label{font-size:16px;color:var(--text-label);white-space:nowrap;font-weight:600;text-transform:uppercase;}
.pc-inp{background:transparent;border:none;border-bottom:1px solid var(--border-light);color:var(--accent2);font-family:'Share Tech Mono',monospace;font-size:19px;text-align:right;padding:2px 4px;width:100%;}
.pc-inp:focus{outline:none;border-bottom-color:var(--accent);}
.pc-inp::-webkit-outer-spin-button,.pc-inp::-webkit-inner-spin-button{-webkit-appearance:none;}
.pc-inp{-moz-appearance:textfield;}
.pc-footer-calc-row{display:flex;justify-content:space-between;align-items:center;padding-top:6px;border-top:1px solid var(--border);}
.pc-qty-note{font-size:17px;color:#ffffff;font-family:'Share Tech Mono',monospace;}
.pc-total-val{font-family:'Share Tech Mono',monospace;font-size:24px;font-weight:bold;color:#f0a020;}
.totals-block{display:flex;flex-direction:column;gap:6px;}
.total-row{display:flex;justify-content:space-between;align-items:center;padding:4px 0;border-bottom:1px solid var(--border);}
.total-row label{font-size:18px;font-weight:600;color:#ffffff;text-transform:uppercase;letter-spacing:0.3px;}
.total-row .val{font-family:'Share Tech Mono',monospace;font-size:21px;color:var(--text);}
.total-row.grand{border-bottom:none;margin-top:5px;}
.total-row.grand label{color:var(--accent2);font-size:20px;}
.total-row.grand .val{font-size:32px;color:var(--accent2);font-weight:bold;}
.total-input-wrap{display:flex;align-items:center;gap:8px;}
.total-inline-input{background:var(--input-bg);border:1px solid var(--border);border-radius:3px;color:var(--accent2);font-family:'Share Tech Mono',monospace;font-size:20px;text-align:right;padding:7px 12px;width:125px;}
.total-inline-input:focus{outline:none;border-color:var(--accent);background:#0d1520;}
.total-inline-input::-webkit-outer-spin-button,.total-inline-input::-webkit-inner-spin-button{-webkit-appearance:none;}
.total-inline-input{-moz-appearance:textfield;}
.paymodes-block{display:flex;flex-direction:column;gap:7px;}
.paymodes-title{font-size:17px;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;color:var(--text-label);border-bottom:1px solid var(--border);padding-bottom:6px;margin-bottom:2px;}
.paymode-entry{display:grid;grid-template-columns:1fr 120px 32px;align-items:center;gap:7px;}
.paymode-select{background:var(--input-bg);border:1px solid var(--border);border-radius:4px;color:var(--text);font-size:19px;padding:9px 13px;width:100%;}
.paymode-select:focus{outline:none;border-color:var(--accent);}
.paymode-amount{background:var(--input-bg);border:1px solid var(--border);border-radius:4px;color:var(--accent2);font-size:20px;font-family:'Share Tech Mono',monospace;text-align:right;padding:9px 13px;width:100%;}
.paymode-amount:focus{outline:none;border-color:var(--accent);}
.paymode-amount::placeholder{color:var(--text-label);}
.paymode-remove{background:transparent;border:1px solid var(--border);border-radius:4px;color:var(--btn-red);font-size:19px;width:34px;height:34px;cursor:pointer;display:flex;align-items:center;justify-content:center;}
.paymode-remove:hover{background:rgba(224,64,64,0.15);border-color:var(--btn-red);}
.paymode-add-btn{background:transparent;border:1px dashed var(--border-light);border-radius:4px;color:var(--accent);font-size:18px;font-weight:600;padding:6px 12px;cursor:pointer;width:100%;text-align:center;}
.paymode-add-btn:hover{background:rgba(0,170,255,0.08);border-color:var(--accent);}
.balance-row{display:flex;justify-content:space-between;align-items:center;margin-top:5px;padding:7px 12px;background:rgba(0,212,170,0.07);border:1px solid rgba(0,212,170,0.28);border-radius:4px;}
.balance-row label{font-size:18px;font-weight:700;color:var(--accent2);text-transform:uppercase;}
.balance-val{font-family:'Share Tech Mono',monospace;font-size:29px;font-weight:bold;color:var(--accent2);}
.btn-bar{background:var(--header-bg);border:1px solid var(--border);border-top:none;border-radius:0 0 6px 6px;padding:11px 20px;display:flex;gap:10px;flex-wrap:wrap;}
.btn{padding:13px 34px;border:none;border-radius:4px;font-size:20px;font-weight:700;letter-spacing:0.5px;cursor:pointer;transition:filter 0.15s,transform 0.1s;font-family:'Barlow',sans-serif;}
.btn:hover{filter:brightness(1.15);}
.btn:active{transform:scale(0.97);}
.btn-confirm{background:var(--btn-green);color:#000;}
.btn-print{background:var(--btn-blue);color:#fff;}
.btn-clear{background:var(--btn-gray);color:#fff;}
.btn-delete{background:var(--btn-red);color:#fff;}
.btn-bar .spacer{flex:1;}
.wa-float{position:fixed;bottom:28px;right:28px;width:62px;height:62px;background:#25D366;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 20px rgba(37,211,102,0.5);cursor:pointer;z-index:9998;transition:transform 0.2s;border:none;}
.wa-float:hover{transform:scale(1.12);}
.wa-float svg{width:34px;height:34px;fill:#fff;}
.wa-float::before{content:'';position:absolute;inset:-4px;border-radius:50%;border:3px solid rgba(37,211,102,0.4);animation:waPulse 2s ease-out infinite;}
@keyframes waPulse{0%{transform:scale(1);opacity:1}100%{transform:scale(1.5);opacity:0}}
.wa-float-tooltip{position:fixed;bottom:40px;right:108px;background:#075E54;color:#fff;font-size:16px;font-weight:600;padding:6px 14px;border-radius:20px;white-space:nowrap;opacity:0;pointer-events:none;transition:opacity 0.2s;z-index:9997;}
.wa-float:hover + .wa-float-tooltip{opacity:1;}
.wa-spin{display:inline-block;animation:waSpin 0.7s linear infinite;font-size:26px;line-height:1;}
@keyframes waSpin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);backdrop-filter:blur(3px);z-index:9999;align-items:center;justify-content:center;}
.modal-overlay.visible{display:flex;}
.modal-box{background:#162030;border:1px solid var(--border-light);border-top:3px solid var(--accent);border-radius:8px;min-width:360px;max-width:480px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.7);overflow:hidden;}
.modal-box.modal-confirm{border-top-color:#f0a020;}
.modal-header{display:flex;align-items:center;gap:10px;padding:16px 20px 12px;border-bottom:1px solid var(--border);}
.modal-icon{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0;}
.modal-icon.icon-alert{background:rgba(0,170,255,0.15);border:1px solid rgba(0,170,255,0.3);color:var(--accent);}
.modal-icon.icon-warn{background:rgba(240,160,32,0.15);border:1px solid rgba(240,160,32,0.3);color:#f0a020;}
.modal-icon.icon-error{background:rgba(224,64,64,0.15);border:1px solid rgba(224,64,64,0.3);color:var(--btn-red);}
.modal-title{font-size:20px;font-weight:700;text-transform:uppercase;color:var(--text);font-family:'Share Tech Mono',monospace;}
.modal-body{padding:22px 24px;font-size:19px;color:#ffffff;line-height:1.6;}
.modal-footer{padding:12px 20px 18px;display:flex;justify-content:flex-end;gap:8px;}
.modal-btn{padding:11px 30px;border:none;border-radius:4px;font-size:19px;font-weight:700;cursor:pointer;font-family:'Barlow',sans-serif;}
.modal-btn-ok{background:var(--accent);color:#000;}
.modal-btn-yes{background:#f0a020;color:#000;}
.modal-btn-cancel{background:var(--btn-gray);color:#fff;}

/* ── WhatsApp Phone Modal ── */
@keyframes waPop{
    from{opacity:0;transform:scale(0.82) translateY(24px);}
    to{opacity:1;transform:scale(1) translateY(0);}
}
@keyframes waShine{
    0%{left:-60%;}100%{left:120%;}
}
.wa-phone-overlay{
    position:fixed;inset:0;
    background:rgba(0,0,0,0.78);
    backdrop-filter:blur(7px);
    z-index:99999;
    display:flex;align-items:center;justify-content:center;
}
.wa-phone-box{
    background:linear-gradient(155deg,#0d1f33 0%,#0a1828 60%,#061220 100%);
    border:1px solid rgba(37,211,102,0.25);
    border-top:3px solid #25D366;
    border-radius:16px;
    width:440px;
    max-width:94vw;
    box-shadow:0 40px 100px rgba(0,0,0,0.8),0 0 0 1px rgba(37,211,102,0.08),inset 0 1px 0 rgba(255,255,255,0.04);
    overflow:hidden;
    animation:waPop 0.28s cubic-bezier(0.34,1.56,0.64,1) both;
}
.wa-phone-header{
    padding:20px 24px 16px;
    display:flex;align-items:center;gap:14px;
    border-bottom:1px solid rgba(255,255,255,0.06);
    position:relative;
    overflow:hidden;
}
.wa-phone-header::after{
    content:'';
    position:absolute;top:0;left:-60%;width:40%;height:100%;
    background:linear-gradient(90deg,transparent,rgba(37,211,102,0.06),transparent);
    animation:waShine 2.5s ease-in-out 0.4s 1;
}
.wa-phone-icon-wrap{
    width:50px;height:50px;flex-shrink:0;
    background:radial-gradient(circle at 40% 40%,#2de070,#1aad4e);
    border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    box-shadow:0 6px 24px rgba(37,211,102,0.45),0 0 0 3px rgba(37,211,102,0.12);
}
.wa-phone-icon-wrap svg{width:26px;height:26px;fill:#fff;}
.wa-phone-header-text{}
.wa-phone-title{
    font-size:21px;font-weight:700;
    color:#e0f4ff;
    font-family:'Share Tech Mono',monospace;
    letter-spacing:0.6px;
    line-height:1.2;
}
.wa-phone-subtitle{
    font-size:15px;color:#3a7a60;
    margin-top:3px;font-family:'Barlow',sans-serif;
}
.wa-phone-body{padding:22px 24px 16px;}
.wa-phone-label{
    display:block;
    font-size:14px;font-weight:700;
    text-transform:uppercase;letter-spacing:1.2px;
    color:#2e7a55;
    margin-bottom:10px;
    font-family:'Barlow',sans-serif;
}
.wa-phone-input-wrap{
    position:relative;
    display:flex;align-items:center;
    background:#071018;
    border:1.5px solid rgba(37,211,102,0.2);
    border-radius:10px;
    overflow:hidden;
    transition:border-color 0.2s,box-shadow 0.2s;
}
.wa-phone-input-wrap:focus-within{
    border-color:#25D366;
    box-shadow:0 0 0 3px rgba(37,211,102,0.12),0 4px 20px rgba(37,211,102,0.08);
}
.wa-phone-prefix{
    padding:0 12px 0 16px;
    font-family:'Share Tech Mono',monospace;
    font-size:24px;
    color:#25D366;
    font-weight:bold;
    flex-shrink:0;
    border-right:1px solid rgba(37,211,102,0.15);
    height:56px;
    display:flex;align-items:center;
    background:rgba(37,211,102,0.04);
}
#waPhoneInp{
    flex:1;
    background:transparent;
    border:none;
    color:#c8eeff;
    font-family:'Share Tech Mono',monospace;
    font-size:24px;
    padding:14px 16px;
    letter-spacing:1px;
    caret-color:#25D366;
}
#waPhoneInp:focus{outline:none;}
#waPhoneInp::placeholder{color:#1e4a35;letter-spacing:0.5px;}
.wa-phone-hint{
    margin-top:10px;
    font-size:14px;
    color:#1e5a40;
    font-family:'Barlow',sans-serif;
    display:flex;align-items:center;gap:6px;
}
.wa-phone-hint-flag{font-size:18px;}
.wa-phone-hint code{
    background:rgba(37,211,102,0.08);
    border:1px solid rgba(37,211,102,0.15);
    border-radius:4px;
    padding:1px 6px;
    font-family:'Share Tech Mono',monospace;
    font-size:14px;
    color:#3aaa70;
}
.wa-phone-footer{
    padding:8px 24px 22px;
    display:flex;gap:10px;justify-content:flex-end;
    border-top:1px solid rgba(255,255,255,0.04);
}
.wa-btn-cancel{
    background:rgba(255,255,255,0.04);
    border:1px solid rgba(255,255,255,0.08);
    border-radius:8px;
    color:#8ab0c0;
    font-size:17px;font-weight:700;
    padding:12px 24px;
    cursor:pointer;
    font-family:'Barlow',sans-serif;
    transition:background 0.15s,color 0.15s;
}
.wa-btn-cancel:hover{background:rgba(255,255,255,0.08);color:#aaccdd;}
.wa-btn-open{
    background:linear-gradient(135deg,#25D366,#1aad4e);
    border:none;
    border-radius:8px;
    color:#000;
    font-size:18px;font-weight:700;
    padding:12px 28px;
    cursor:pointer;
    font-family:'Barlow',sans-serif;
    display:flex;align-items:center;gap:8px;
    box-shadow:0 6px 20px rgba(37,211,102,0.35);
    transition:filter 0.15s,transform 0.1s,box-shadow 0.15s;
}
.wa-btn-open:hover{filter:brightness(1.1);box-shadow:0 8px 28px rgba(37,211,102,0.5);}
.wa-btn-open:active{transform:scale(0.97);}
.wa-btn-open svg{width:18px;height:18px;fill:#000;}

@media print {
    body{background:#fff;color:#000;padding:0;}
    .topbar,.tabs,.btn-bar,.modal-overlay,.wa-float,.wa-float-tooltip{display:none!important;}
    .container{max-width:100%;}
    .header-panel,.footer-area,.grid-area{border:1px solid #ccc;background:#fff!important;}
    .grid-table thead{background:#eee!important;}
    .grid-table thead th,.grid-table tbody td input,.grid-table tbody td select,.hfield input,.hfield select{color:#000;}
    .grid-table tbody tr{background:#fff!important;}
    .shop-name-block h1{color:#000;}
    .invoice-badge{background:#eee;color:#000;border-color:#999;}
    .total-row .val,.total-row.grand .val{color:#000;}
    .total-inline-input{color:#000;background:#fff;border-color:#ccc;}
    .rm-size-btn,.add-size-btn{display:none!important;}
    .pc-footer-block{display:none!important;}
}
</style>
</head>
<body>
<datalist id="fabric-suggestions">
    <option value="P.P"><option value="H/C"><option value="Mpp"><option value="DT (Dot Net)">
    <option value="S.P"><option value="Metti"><option value="Peanut"><option value="Shirting">
    <option value="Suiting"><option value="Cotton"><option value="Dri-Fit">
</datalist>
<datalist id="bag-fabric-suggestions">
    <option value="Leather"><option value="Rexine">
</datalist>
<div class="modal-overlay" id="customModal">
    <div class="modal-box" id="modalBox">
        <div class="modal-header">
            <div class="modal-icon" id="modalIcon">⚠</div>
            <div class="modal-title" id="modalTitle">Notice</div>
        </div>
        <div class="modal-body" id="modalBody"></div>
        <div class="modal-footer" id="modalFooter"></div>
    </div>
</div>
<div class="container">
    <div class="topbar">
        <span class="brand">⚡ SARTHI SPORTS WEAR — ORDER MANAGEMENT</span>
        <div class="info-pills">
            <span class="pill">User: <span>ADMIN</span></span>
            <span class="pill">Date: <span id="currentDate"></span></span>
            <span class="pill">Time: <span id="currentTime"></span></span>
        </div>
    </div>
    <?php if ($success_msg): ?><div class="banner success">✔ <?= htmlspecialchars($success_msg) ?></div><?php endif; ?>
    <?php if ($error_msg): ?><div class="banner error">✕ <?= $error_msg ?></div><?php endif; ?>
    <div class="header-panel">
        <div class="shop-name-block">
            <h1>Sarthi Sports Wear</h1>
            <p>Ph: 0762-0425141 | Mob: 9422107750</p>
        </div>
        <div class="hfield"><label>Entry No.</label><div class="invoice-badge" id="invoiceDisplay" style="color:#ffffff !important;">—</div></div>
        <div class="hfield"><label>Invoice No.</label><input type="text" id="invoice_no" placeholder="Auto" readonly style="color:#ffffff !important;"></div>
        <div class="hfield"><label>Customer Name</label><input type="text" id="customer_name" placeholder="Enter name" style="color:#ffffff !important;"></div>
        <div class="hfield"><label>Order Date</label><input type="date" id="order_date" style="color:#ffffff !important;"></div>
        <div class="hfield"><label>Delivery Date</label><input type="date" id="delivery_date" style="color:#ffffff !important;"></div>
        <div class="hfield"><label>Advance Date</label><input type="date" id="advance_date" style="color:#ffffff !important;"></div>
        <input type="hidden" id="bill_type" value="Regular Bill">
        <input type="hidden" id="bill_status" value="Pending">
    </div>
    <div class="tabs"><div class="tab active">Detail Stock</div></div>
    <div class="grid-area">
        <table class="grid-table" id="orderTable">
            <thead><tr>
                <th>#</th><th>Product</th>
                <th>Size — Pattern — Fabric — Color — MRP — Dis% — Net Rate — Qty — Amount</th>
                <th>Notes</th>
            </tr></thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
    <div class="footer-area">
        <div class="summary-col">
            <div class="sfield">
                <label>Remarks / Notes</label>
                <input type="text" id="remarks" placeholder="Special instructions...">
            </div>
            <div class="qty-items-row">
                <div class="sfield"><label>Total Qty</label><input type="text" id="total_qty" readonly></div>
                <div class="sfield"><label>Total Items</label><input type="text" id="total_items" readonly></div>
            </div>
            <div class="pc-footer-block">
                <div class="pc-footer-title">🖨 T-Shirt Print Charges</div>
                <div class="pc-footer-inputs">
                    <span class="pc-inp-label">Logo ₹/pc</span>
                    <input type="number" id="pc_logo" class="pc-inp" placeholder="0" min="0" step="0.01" oninput="updateTotals()">
                    <span class="pc-inp-label" style="padding-left:6px;">Back ₹/pc</span>
                    <input type="number" id="pc_back" class="pc-inp" placeholder="0" min="0" step="0.01" oninput="updateTotals()">
                </div>
                <div class="pc-footer-calc-row">
                    <span class="pc-qty-note" id="pc_qty_note">× 0 T-Shirt pcs</span>
                    <span class="pc-total-val" id="pc_footer_total">₹ 0.00</span>
                </div>
            </div>
        </div>
        <div class="totals-block">
            <div class="total-row"><label>Gross Amount</label><span class="val" id="gross_amt">₹ 0.00</span></div>
            <div class="total-row" id="pc_total_row" style="display:none;"><label>Print Charges</label><span class="val" id="pc_total_val" style="color:#f0a020;">+ ₹ 0.00</span></div>
            <div class="total-row"><label>Discount %</label><div class="total-input-wrap"><input type="number" id="disc_input" class="total-inline-input" min="0" max="100" step="0.01" placeholder="0" oninput="updateTotals()"><span class="val" id="disc_amt">- ₹ 0.00</span></div></div>
            <div class="total-row"><label>Discount ₹</label><div class="total-input-wrap"><input type="number" id="disc_flat_input" class="total-inline-input" min="0" step="0.01" placeholder="0" oninput="updateTotals()"><span class="val" id="disc_flat_amt">- ₹ 0.00</span></div></div>
            <div class="total-row"><label>Charges (GST %)</label><div class="total-input-wrap"><input type="number" id="charges_input" class="total-inline-input" min="0" max="100" step="0.01" placeholder="%" oninput="updateTotals()"><span class="val" id="charges_amt">₹ 0.00</span></div></div>
            <div class="total-row grand"><label>NET AMOUNT</label><span class="val" id="net_amt">₹ 0.00</span></div>
        </div>
        <div class="paymodes-block">
            <div class="paymodes-title">💳 Payment Modes</div>
            <div id="paymode-entries"></div>
            <button class="paymode-add-btn" onclick="addPaymodeRow()">+ Add Payment Mode</button>
            <div class="balance-row">
                <label>Balance Due</label>
                <span class="balance-val" id="balance_amt">₹ 0.00</span>
            </div>
        </div>
    </div>
    <div class="btn-bar">
        <button class="btn btn-confirm" onclick="confirmOrder()">✔ CONFIRM ORDER</button>
        <button class="btn btn-print"   onclick="printOrder()">🖨 PRINT</button>
        <button class="btn btn-print"   onclick="printMasterCopy()">🖨 MASTER COPY</button>
        <button class="btn btn-clear"   onclick="clearForm()">↺ CLEAR</button>
        <div class="spacer"></div>
        <button class="btn btn-delete"  onclick="deleteRow()">✕ DELETE ROW</button>
        <button class="btn btn-confirm" onclick="addRow();" style="background:#006699;">+ ADD ROW</button>
    </div>
</div>
<button class="wa-float" id="waBtn" onclick="sendWhatsApp()">
    <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</button>
<span class="wa-float-tooltip">Send Bill PDF via WhatsApp</span>
<script>
// ─── Modal Helpers ───────────────────────────────────────────────────────
function showAlert(message,title='Notice',type='info'){
    return new Promise(resolve=>{
        const cfg={info:{icon:'ℹ',cls:'icon-alert'},warn:{icon:'⚠',cls:'icon-warn'},error:{icon:'✕',cls:'icon-error'}}[type]||{icon:'ℹ',cls:'icon-alert'};
        document.getElementById('modalIcon').textContent=cfg.icon;
        document.getElementById('modalIcon').className=`modal-icon ${cfg.cls}`;
        document.getElementById('modalBox').className='modal-box';
        document.getElementById('modalTitle').textContent=title;
        document.getElementById('modalBody').textContent=message;
        document.getElementById('modalFooter').innerHTML=`<button class="modal-btn modal-btn-ok" id="modalOkBtn">OK</button>`;
        document.getElementById('customModal').classList.add('visible');
        document.getElementById('modalOkBtn').focus();
        document.getElementById('modalOkBtn').onclick=()=>{document.getElementById('customModal').classList.remove('visible');resolve(true);};
    });
}
function showConfirm(message,title='Confirm'){
    return new Promise(resolve=>{
        document.getElementById('modalIcon').textContent='?';
        document.getElementById('modalIcon').className='modal-icon icon-warn';
        document.getElementById('modalBox').className='modal-box modal-confirm';
        document.getElementById('modalTitle').textContent=title;
        document.getElementById('modalBody').textContent=message;
        document.getElementById('modalFooter').innerHTML=`<button class="modal-btn modal-btn-cancel" id="modalCancelBtn">Cancel</button><button class="modal-btn modal-btn-yes" id="modalYesBtn">Yes, Proceed</button>`;
        document.getElementById('customModal').classList.add('visible');
        document.getElementById('modalYesBtn').focus();
        document.getElementById('modalYesBtn').onclick=()=>{document.getElementById('customModal').classList.remove('visible');resolve(true);};
        document.getElementById('modalCancelBtn').onclick=()=>{document.getElementById('customModal').classList.remove('visible');resolve(false);};
    });
}

// ─── WhatsApp Phone Number Modal ─────────────────────────────────────────────
function askPhoneNumber(){
    return new Promise(resolve=>{
        const existing=document.getElementById('waPhoneModal');
        if(existing)existing.remove();

        const overlay=document.createElement('div');
        overlay.id='waPhoneModal';
        overlay.className='wa-phone-overlay';

        overlay.innerHTML=`
        <div class="wa-phone-box">
            <div class="wa-phone-header">
                <div class="wa-phone-icon-wrap">
                    <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </div>
                <div class="wa-phone-header-text">
                    <div class="wa-phone-title">OPEN WHATSAPP</div>
                    <div class="wa-phone-subtitle">Enter number to open their chat inbox</div>
                </div>
            </div>
            <div class="wa-phone-body">
                <label class="wa-phone-label">Mobile Number with Country Code</label>
                <div class="wa-phone-input-wrap">
                    <span class="wa-phone-prefix">+</span>
                    <input id="waPhoneInp" type="tel" value="91" placeholder="919422107750" autocomplete="tel" spellcheck="false">
                </div>
                <div class="wa-phone-hint">
                    <span class="wa-phone-hint-flag">🇮🇳</span>
                    India: <code>91</code> + 10-digit number &nbsp;·&nbsp; e.g. <code>919422107750</code>
                </div>
            </div>
            <div class="wa-phone-footer">
                <button class="wa-btn-cancel" id="waPhoneCancelBtn">Cancel</button>
                <button class="wa-btn-open" id="waPhoneOpenBtn">
                    <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Open WhatsApp
                </button>
            </div>
        </div>`;

        function submit(){
            const val=document.getElementById('waPhoneInp').value.trim().replace(/\D/g,'');
            overlay.remove();
            resolve(val||null);
        }
        function cancel(){overlay.remove();resolve(null);}

        overlay.querySelector('#waPhoneCancelBtn').onclick=cancel;
        overlay.querySelector('#waPhoneOpenBtn').onclick=submit;
        overlay.addEventListener('click',e=>{if(e.target===overlay)cancel();});
        overlay.addEventListener('keydown',e=>{
            if(e.key==='Enter'){e.preventDefault();submit();}
            if(e.key==='Escape'){e.preventDefault();cancel();}
        });

        document.body.appendChild(overlay);
        setTimeout(()=>{
            const inp=document.getElementById('waPhoneInp');
            if(inp){inp.focus();inp.select();}
        },80);
    });
}

// ─── Payment Modes ───────────────────────────────────────────────────────────
const PAY_MODE_OPTIONS=['Cash','UPI','Card','Cheque','Advance','Online','NEFT/RTGS','Other'];
let paymodeCounter=0;
function addPaymodeRow(defaultMode=''){
{
}

    const container=document.getElementById('paymode-entries');
    const id=++paymodeCounter;
    const opts=PAY_MODE_OPTIONS.map(m=>`<option value="${m}" ${m===defaultMode?'selected':''}>${m}</option>`).join('');
    const div=document.createElement('div');
    div.className='paymode-entry';
    div.dataset.pmid=id;
    div.innerHTML=`<select class="paymode-select" onchange="updateBalance()"><option value="">— Select Mode —</option>${opts}</select><input type="number" class="paymode-amount" placeholder="0.00" min="0" oninput="updateBalance()"><button class="paymode-remove" onclick="removePaymodeRow(${id})">✕</button>`;
    container.appendChild(div);
    div.querySelector('.paymode-select').focus();
    updateBalance();
}
function removePaymodeRow(id){document.querySelector(`[data-pmid="${id}"]`)?.remove();updateBalance();}
function resetPaymodes(){document.getElementById('paymode-entries').innerHTML='';paymodeCounter=0;updateBalance();}

// ─── Product / Size Data ─────────────────────────────────────────────────────
const productData={
    tshirt:{sizes:["18","20","22","24","26","28","30","32","34","36","38","40","42","44","46"],patterns:["Round Neck","kollar","Plain Pippin","Double Pippin"],hasPrint:true},
    tracksuit:{sizes:["18","20","22","24","26","28","30","32","34","36","38","40","42","44","46"],patterns:["Plain","Single Pippin","Double Pippin"]},
    halfpant:{sizes:["10","12","14","16","18","20","22","24","26","28","30"],patterns:["Plain","Lining","Check"]},
    Lower:{sizes:["18","20","22","24","26","28","30","32","34","36","38","40","42","44","46"],patterns:["Plain","Single Pippin","Double Pippin"]},
    Blazer:{sizes:["26","28","30","32","34","36","38","40","42","44","46"],patterns:["Plain","Single Button","Double Button"]},
    tunic:{sizes:["20","22","24","26","28","30","32","34","36","38","40","42","44","46"],patterns:["Plain","Printed","Embroidered"]},
    frock:{sizes:["20","22","24","26","28","30","32","34","36","38","40","42","44","46"],patterns:["Plain","Printed","Flared"]},
    skirt:{sizes:["10","12","14","16","18","20","22","24","26","28","30","32","34","36"],patterns:["Plain","Pleated","Wrap Around"]},
    salwar_suit:{sizes:["32","34","36","38","40","42","44","46"],patterns:["Plain","Printed","Embroidered"]},
    waist_coat:{sizes:["26","28","30","32","34","36","38","40","42","44","46"],patterns:["Plain","Single Button","Double Button"]},
    jacket:{sizes:["18","20","22","24","26","28","30","32","34","36","38","40","42","44","46"],patterns:["Plain","Zipper","Button","Hooded"]},
    tie:{sizes:["10","12","14","16","18","20"],patterns:["Plain","Cross Lining","Work"],isTie:true},
    belt:{sizes:[],patterns:[]},
    socks:{sizes:["0","1","2","3","4","5","6","7","Free Size"],patterns:["Plain","Two Strip","Name"]},
    shoes:{sizes:[],patterns:["Velcro","No Velcro"],isShoes:true},
 bag:{sizes:[],patterns:[],isBag:true}
};
const SHOES_SIZES={kids:["4","5","6","7","8","9","10","11","12","13"],adults:["1","2","3","4","5","6","7","8","9","10"]};
const PRODUCTS=["","tshirt","tracksuit","halfpant","Lower","Blazer","tunic","frock","skirt","salwar_suit","waist_coat","jacket","tie","belt","socks","shoes","bag"];
const COLORS=["","Navy Blue","Black","Light Grey","Dark Grey","White","Red","Yellow","Blue","Green","Samre","Sky Blue","Kiwi (Sea Green)","Maroon","Orange","Lemon","Multi Color","Custom"];
const colorOpts=COLORS.map(c=>`<option value="${c}">${c||'-- Color --'}</option>`).join('');

// ─── Clock & Init ────────────────────────────────────────────────────────────
const NUM_ROWS=12;
// ── FIX: unique ID counter for data-row / srp-{id} panel wiring ──
let _rowIdCounter=0;
let invoiceNum=Math.floor(18000+Math.random()*1000);
function pad(n){return String(n).padStart(2,'0');}
function tick(){
    const d=new Date();
    document.getElementById('currentDate').textContent=`${pad(d.getDate())}/${pad(d.getMonth()+1)}/${d.getFullYear()}`;
    document.getElementById('currentTime').textContent=`${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
}
setInterval(tick,1000);tick();
(function init(){
    document.getElementById('order_date').value=new Date().toISOString().split('T')[0];
    document.getElementById('invoice_no').value='INV-'+invoiceNum;
    document.getElementById('invoiceDisplay').textContent=invoiceNum;
    addPaymodeRow('Cash');
    buildTable();
})();

// ─── Table Builder ───────────────────────────────────────────────────────────
// ── FIX: reset counter on full rebuild; call reindexRows after build ──
function reindexRows(){document.querySelectorAll('#tableBody > tr').forEach((tr,i)=>{const c=tr.querySelector('.row-serial');if(c)c.textContent=i+1;});}
function buildTable(){
    const tbody=document.getElementById('tableBody');
    tbody.innerHTML='';
    _rowIdCounter=0;
    for(let i=1;i<=NUM_ROWS;i++){
        const uid=++_rowIdCounter;
        const tr=document.createElement('tr');
        tr.dataset.row=uid;
        const prodOpts=PRODUCTS.map(p=>{
            let label=p?p.charAt(0).toUpperCase()+p.slice(1).replace(/_/g,' '):'-- Select --';
            if(p==='salwar_suit')label='Salwar Suit';
            if(p==='waist_coat')label='Waist Coat';
            return`<option value="${p}">${label}</option>`;
        }).join('');
        tr.innerHTML=`<td class="row-serial"></td><td><select onchange="onProductChange(this)">${prodOpts}</select></td><td style="min-width:640px;padding:0;"><div class="size-rate-panel" id="srp-${uid}"><table><thead><tr><th style="width:56px;">Size</th><th style="width:120px;">Pattern</th><th style="width:110px;">Fabric</th><th style="width:120px;">Color</th><th style="width:54px;">Qty</th><th style="width:48px;">Dis%</th><th style="width:78px;">MRP ₹</th><th style="width:82px;">Net Rate</th><th style="width:90px;">Amount</th><th style="width:24px;"></th></tr></thead><tbody class="sr-body"></tbody></table><button class="add-size-btn" onclick="addNextSizeRow(${uid})">+ Size</button></div></td><td style="min-width:100px;"><input type="text" class="row-notes" placeholder="Notes..."></td>`;
        tr.addEventListener('click',()=>{
            document.querySelectorAll('#tableBody tr').forEach(r=>r.classList.remove('active-row'));
            tr.classList.add('active-row');
        });
        tbody.appendChild(tr);
    }
    reindexRows();
}
function addRow(){
    const tbody=document.getElementById('tableBody');
    const uid=++_rowIdCounter;
    const tr=document.createElement('tr');
    tr.dataset.row=uid;
    const prodOpts=PRODUCTS.map(p=>{
        let label=p?p.charAt(0).toUpperCase()+p.slice(1).replace(/_/g,' '):'-- Select --';
        if(p==='salwar_suit')label='Salwar Suit';
        if(p==='waist_coat')label='Waist Coat';
        return`<option value="${p}">${label}</option>`;
    }).join('');
    tr.innerHTML=`
        <td class="row-serial"></td>
        <td><select onchange="onProductChange(this)">${prodOpts}</select></td>
        <td style="min-width:640px;padding:0;">
            <div class="size-rate-panel" id="srp-${uid}">
                <table>
                <thead><tr>
                    <th style="width:56px;">Size</th>
                    <th style="width:120px;">Pattern</th><th style="width:110px;">Fabric</th><th style="width:120px;">Color</th>
                    <th style="width:54px;">Qty</th><th style="width:48px;">Dis%</th><th style="width:78px;">MRP ₹</th><th style="width:82px;">Net Rate</th>
                    <th style="width:90px;">Amount</th><th style="width:24px;"></th>
                </tr></thead>
                <tbody class="sr-body"></tbody></table>
                <button class="add-size-btn" onclick="addNextSizeRow(${uid})">+ Size</button>
            </div>
        </td>
        <td style="min-width:100px;"><input type="text" class="row-notes" placeholder="Notes..."></td>`;
    tr.addEventListener('click',()=>{
        document.querySelectorAll('#tableBody tr').forEach(r=>r.classList.remove('active-row'));
        tr.classList.add('active-row');
    });
    tbody.appendChild(tr);
    reindexRows();
}

// ─── Size/Rate Rows ───────────────────────────────────────────────────────────
let srRowCounter=0;
function addSizeRateRow(rowIdx,sizeValue='',rateValue='',doFocus=true){
    const panel=document.getElementById(`srp-${rowIdx}`);if(!panel)return;
    const srBody=panel.querySelector('.sr-body');
    const id=++srRowCounter;
    let patternOpts='<option value="">— Pattern —</option>';
    try{const pats=JSON.parse(panel.dataset.patterns||'[]');patternOpts+=pats.map(p=>`<option>${p}</option>`).join('');}catch(e){}
    const isBag=panel.dataset.isBag==='1';
    const tr=document.createElement('tr');tr.dataset.srid=id;
    tr.innerHTML=`
        <td><input type="text" class="sr-size" placeholder="Size" value="${sizeValue}" onkeydown="srAutoJump(event,this,'size')" style="width:54px;background:transparent;border:none;color:var(--text);font-size:19px;font-family:'Share Tech Mono',monospace;padding:4px 6px;"></td>
        <td><select class="sr-pattern sr-pattern-sel">${patternOpts}</select></td>
        <td><input type="text" class="sr-fabric sr-fabric-inp" placeholder="${isBag?'Leather/Rexine':'Fabric...'}" list="${isBag?'bag-fabric-suggestions':'fabric-suggestions'}" autocomplete="off"></td>
        <td><select class="sr-color sr-color-sel">${colorOpts}</select></td>
        <td><input type="number" class="sr-qty" placeholder="0" min="0" onkeydown="srAutoJump(event,this,'qty')" oninput="calcSizeRow(this)" style="width:52px;background:transparent;border:none;color:var(--text);font-size:19px;font-family:'Share Tech Mono',monospace;padding:4px 6px;"></td>
        <td><input type="number" class="sr-dis" placeholder="0" value="0" min="0" max="100" onkeydown="srAutoJump(event,this,'dis')" oninput="calcSizeRow(this)" style="width:46px;background:transparent;border:none;color:var(--text);font-size:19px;font-family:'Share Tech Mono',monospace;padding:4px 6px;"></td>
        <td><input type="number" class="sr-mrp" placeholder="0" value="${rateValue}" min="0" onkeydown="srAutoJump(event,this,'mrp')" oninput="calcSizeRow(this)" style="width:76px;background:transparent;border:none;color:var(--text);font-size:19px;font-family:'Share Tech Mono',monospace;padding:4px 6px;"></td>
        <td><input type="number" class="sr-netrate" placeholder="0.00" readonly style="width:80px;background:transparent;border:none;color:var(--accent2);font-size:19px;font-family:'Share Tech Mono',monospace;padding:4px 6px;"></td>
        <td style="width:90px;text-align:right;">
<input type="number" class="sr-amount" placeholder="0.00" readonly
style="width:83px;background:transparent;border:none;color:var(--accent2);
font-size:17px;font-family:'Share Tech Mono',monospace;padding:3px 5px;
font-weight:bold;text-align:right;">
</td>
        <td><button class="rm-size-btn" onclick="removeSizeRateRow(${id})">✕</button></td>`;
    srBody.appendChild(tr);
    if(doFocus)setTimeout(()=>tr.querySelector('.sr-size').focus(),30);
}
function addNextSizeRow(rowIdx){
    const panel=document.getElementById(`srp-${rowIdx}`);if(!panel)return;
    let allSizes=[];try{allSizes=JSON.parse(panel.dataset.allSizes||'[]');}catch(e){}
    let nextIdx=parseInt(panel.dataset.nextSizeIdx||'0',10);
    const nextSize=(allSizes.length>0&&nextIdx<allSizes.length)?allSizes[nextIdx]:'';
    panel.dataset.nextSizeIdx=String(nextIdx+1);
    let autoMRP='';
    const filled=Array.from(panel.querySelectorAll('.sr-body tr')).map(r=>r.querySelector('.sr-mrp')?.value.trim()).filter(v=>v!==''&&!isNaN(parseFloat(v))).map(v=>parseFloat(v));
    if(filled.length>=2){const gap=filled[filled.length-1]-filled[filled.length-2];autoMRP=(filled[filled.length-1]+gap).toFixed(0);}
    addSizeRateRow(rowIdx,nextSize,autoMRP,true);
}
function removeSizeRateRow(srid){document.querySelector(`[data-srid="${srid}"]`)?.remove();updateTotals();}
function calcSizeRow(inp){
    const tr=inp.closest('tr');
    const mrp=parseFloat(tr.querySelector('.sr-mrp')?.value)||0;
    const dis=parseFloat(tr.querySelector('.sr-dis')?.value)||0;
    const qty=parseFloat(tr.querySelector('.sr-qty')?.value)||0;
    const netRate=mrp-(mrp*dis/100);
    const amount=netRate*qty;
    const netEl=tr.querySelector('.sr-netrate');
    const amtEl=tr.querySelector('.sr-amount');
    if(netEl)netEl.value=netRate.toFixed(2);
    if(amtEl)amtEl.value=amount.toFixed(2);
    updateTotals();
}
function srAutoJump(e,inp,fieldType){
    if(e.key==='Tab'||e.key==='Enter'){
        e.preventDefault();
        const tr=inp.closest('tr');
        if(fieldType==='size')tr.querySelector('.sr-qty')?.focus();
        else if(fieldType==='qty')tr.querySelector('.sr-dis')?.focus();
        else if(fieldType==='dis')tr.querySelector('.sr-mrp')?.focus();
        else if(fieldType==='mrp'){
            const nextTr=tr.nextElementSibling;
            if(nextTr){nextTr.querySelector('.sr-size')?.focus();return;}
            const panelEl=inp.closest('.size-rate-panel');
            if(panelEl)addNextSizeRow(parseInt(panelEl.id.replace('srp-',''),10));
        }
    }
}
function onProductChange(sel){
    const tr=sel.closest('tr');
    const prod=sel.value;
    const data=productData[prod]||{sizes:[],patterns:[]};
    const rowIdx=parseInt(tr.dataset.row,10);
    const srp=document.getElementById(`srp-${rowIdx}`);
    if(srp){
        srp.dataset.patterns=JSON.stringify(data.patterns||[]);
        srp.dataset.isBag=data.isBag?'1':'0';
      if(prod){
    srp.classList.add('visible');
    srp.querySelector('.sr-body').innerHTML='';
    const oldSel=srp.querySelector('.shoes-type-sel');if(oldSel)oldSel.remove();
    if(data.isShoes){
        const selBar=document.createElement('div');
        selBar.className='shoes-type-sel';
        selBar.style.cssText='display:flex;gap:0;border-bottom:1px solid var(--border);';
        selBar.innerHTML=`
            <button onclick="selectShoesType(${rowIdx},'kids',this)" style="flex:1;padding:7px 0;background:var(--highlight);border:none;border-right:1px solid var(--border);color:var(--accent);font-size:18px;font-weight:700;cursor:pointer;font-family:Barlow,sans-serif;">👟 Kids (4–13)</button>
            <button onclick="selectShoesType(${rowIdx},'adults',this)" style="flex:1;padding:7px 0;background:transparent;border:none;color:#ffffff;font-size:18px;font-weight:700;cursor:pointer;font-family:Barlow,sans-serif;">👟 Adults (1–10)</button>`;
        srp.insertBefore(selBar,srp.querySelector('table'));
        selectShoesType(rowIdx,'kids',selBar.querySelector('button:first-child'));
    }else if(data.sizes&&data.sizes.length>0){
        srp.dataset.allSizes=JSON.stringify(data.sizes);
        srp.dataset.nextSizeIdx='2';
        data.sizes.slice(0,2).forEach(sz=>addSizeRateRow(rowIdx,sz,'',false));
    }else{
        srp.dataset.allSizes='[]';
        srp.dataset.nextSizeIdx='0';
        addSizeRateRow(rowIdx,'','',false);
    }
}else{
    srp.classList.remove('visible');
}
    }
    updateTotals();
}
function selectShoesType(rowIdx,type,btn){
    const srp=document.getElementById(`srp-${rowIdx}`);if(!srp)return;
    const sizes=SHOES_SIZES[type];
    srp.dataset.allSizes=JSON.stringify(sizes);
    srp.dataset.nextSizeIdx='2';
    srp.querySelector('.sr-body').innerHTML='';
    sizes.slice(0,2).forEach(sz=>addSizeRateRow(rowIdx,sz,'',false));
    srp.querySelectorAll('.shoes-type-sel button').forEach(b=>{b.style.background='transparent';b.style.color='#ffffff';});
    btn.style.background='var(--highlight)';btn.style.color='var(--accent)';
    updateTotals();
}

// ─── TOTALS ───────────────────────────────────────────────────────────────────
function updateTotals(){
    let gross=0,totalQty=0,items=0,tshirtQty=0;

    document.querySelectorAll('#tableBody tr').forEach(tr=>{
        const rowIdx=parseInt(tr.dataset.row,10);
        const srPanel=document.getElementById(`srp-${rowIdx}`);
        const prodSel=tr.querySelector('td:nth-child(2) > select');
        let rowAmt=0,rowQty=0;
        if(srPanel){
            srPanel.querySelectorAll('.sr-amount').forEach(a=>{rowAmt+=parseFloat(a.value)||0;});
            srPanel.querySelectorAll('.sr-qty').forEach(q=>{rowQty+=parseFloat(q.value)||0;});
        }
        if(rowAmt>0){gross+=rowAmt;items++;totalQty+=rowQty;}
        if(prodSel&&prodSel.value==='tshirt')tshirtQty+=rowQty;
    });

    const logo=parseFloat(document.getElementById('pc_logo').value)||0;
    const back=parseFloat(document.getElementById('pc_back').value)||0;
    const totalPrint=(logo+back)*tshirtQty;
    document.getElementById('pc_qty_note').textContent=`× ${tshirtQty} T-Shirt pcs`;
    document.getElementById('pc_footer_total').textContent='₹ '+totalPrint.toFixed(2);
    const pcRow=document.getElementById('pc_total_row');
    const pcVal=document.getElementById('pc_total_val');
    if(totalPrint>0){pcRow.style.display='flex';pcVal.textContent='+ ₹ '+totalPrint.toFixed(2);}
    else{pcRow.style.display='none';}

    const discPct=Math.min(100,Math.max(0,parseFloat(document.getElementById('disc_input').value)||0));
    const discFlat=Math.max(0,parseFloat(document.getElementById('disc_flat_input').value)||0);
    const gstPct=Math.max(0,parseFloat(document.getElementById('charges_input').value)||0);
    const disc=gross*discPct/100;
    const charges=Math.max(0,(gross+totalPrint-disc-discFlat)*gstPct/100);
    const net=Math.max(0,gross+totalPrint-disc-discFlat+charges);

    document.getElementById('gross_amt').textContent='₹ '+gross.toFixed(2);
    document.getElementById('disc_amt').textContent='- ₹ '+disc.toFixed(2);
    document.getElementById('disc_flat_amt').textContent='- ₹ '+discFlat.toFixed(2);
    document.getElementById('charges_amt').textContent=(gstPct>0?gstPct+'% = ':'')+'₹ '+charges.toFixed(2);
    document.getElementById('net_amt').textContent='₹ '+net.toFixed(2);
    document.getElementById('total_qty').value=totalQty;
    document.getElementById('total_items').value=items;

    updateBalance(net);
}

// ─── BALANCE ──────────────────────────────────────────────────────────────────
function updateBalance(netAmount){
    if(netAmount===undefined){
        netAmount=parseFloat(document.getElementById('net_amt').textContent.replace(/[₹ ,]/g,''))||0;
    }
    let paid=0;
    document.querySelectorAll('#paymode-entries .paymode-entry').forEach(row=>{
        const mode=row.querySelector('.paymode-select')?.value;
        const amt=parseFloat(row.querySelector('.paymode-amount')?.value)||0;
        if(mode)paid+=Math.max(0,amt);
    });
    document.getElementById('balance_amt').textContent='₹ '+Math.max(0,netAmount-paid).toFixed(2);
}

// ─── Actions ─────────────────────────────────────────────────────────────────
function deleteRow(){
    const active=document.querySelector('#tableBody tr.active-row');
    // ── FIX: reindexRows after delete so serial numbers stay correct ──
    if(active&&document.querySelectorAll('#tableBody tr').length>1){active.remove();reindexRows();updateTotals();}
}
async function clearForm(){
    if(!await showConfirm('This will erase all entered data. Are you sure?','Clear All Data'))return;
    invoiceNum++;
    document.getElementById('invoice_no').value='INV-'+invoiceNum;
    document.getElementById('invoiceDisplay').textContent=invoiceNum;
    ['customer_name','remarks','advance_date'].forEach(id=>{document.getElementById(id).value='';});
    ['disc_input','disc_flat_input','charges_input','pc_logo','pc_back'].forEach(id=>{document.getElementById(id).value='';});
    resetPaymodes();addPaymodeRow('Cash');buildTable();updateTotals();
}
function printOrder(){
    const items=collectAllItems();
    const custName=document.getElementById('customer_name').value||'—';
    const invNo=document.getElementById('invoice_no').value||'—';
    const orderDate=document.getElementById('order_date').value;
    const delivDate=document.getElementById('delivery_date').value;
    const advDate=document.getElementById('advance_date').value;
    const billType=document.getElementById('bill_type').value;
    const billStatus=document.getElementById('bill_status').value;
    const remarks=document.getElementById('remarks').value;
    const gross=document.getElementById('gross_amt').textContent;
    const discPct=document.getElementById('disc_input').value||'0';
    const discAmt=document.getElementById('disc_amt').textContent;
    const discFlat=document.getElementById('disc_flat_input').value||'0';
    const discFlatAmt=document.getElementById('disc_flat_amt').textContent;
    const chargesAmt=document.getElementById('charges_amt').textContent;
    const net=document.getElementById('net_amt').textContent;
    const balance=document.getElementById('balance_amt').textContent;
    const payModes=collectPaymentModes();
    const logo_pc=parseFloat(document.getElementById('pc_logo').value)||0;
    const back_pc=parseFloat(document.getElementById('pc_back').value)||0;
    const pcTotal=document.getElementById('pc_total_val').textContent||'₹ 0.00';

    function fmtDate(d){if(!d)return '—';const p=d.split('-');return p.length===3?`${p[2]}/${p[1]}/${p[0]}`:d;}

    let itemRows='';
    let srNo=1;
    let grandQty=0;
    items.forEach(item=>{
        const prod=item.product.charAt(0).toUpperCase()+item.product.slice(1).replace(/_/g,' ');
        const sizes=item.sizes||[];
        sizes.forEach((sz,si)=>{
            const qty=sz.qty||0;
            if(!qty)return;
            grandQty+=qty;
            itemRows+=`<tr>
                <td>${si===0?srNo:''}</td>
                <td style="text-align:left;">${si===0?prod:''}</td>
                <td style="text-align:left;">${sz.pattern||item.pattern||''}</td>
                <td style="text-align:left;">${sz.fabric||item.fabric||''}</td>
                <td style="text-align:left;">${sz.color||item.color||''}</td>
                <td>${sz.size||''}</td>
                <td>${qty}</td>
                <td>${sz.mrp?'₹'+Number(sz.mrp).toFixed(2):''}</td>
                <td>${sz.dis?sz.dis+'%':''}</td>
                <td>${sz.netrate?'₹'+Number(sz.netrate).toFixed(2):''}</td>
                <td style="white-space:nowrap;">₹${Number(sz.amount||0).toFixed(2)}</td>
                <td style="text-align:left;">${si===0?item.notes||'':''}</td>
                            </tr>`;
        });
        if(item.print_charges>0){
            itemRows+=`<tr style="background:#fffbe6;">
                <td></td><td style="text-align:left;font-style:italic;color:#000;">↳ Print Charges</td>
                <td colspan="8" style="text-align:left;font-size:17px;color:#000;">Logo ₹${logo_pc}+Back ₹${back_pc} × ${sizes.reduce((s,r)=>s+(r.qty||0),0)} pcs</td>
                <td>₹${Number(item.print_charges).toFixed(2)}</td>
            </tr>`;
        }
        srNo++;
    });

    const payStr=payModes?payModes.split(',').map(p=>`<span style="display:inline-block;background:#d4edda;border:1px solid #333;border-radius:3px;padding:2px 8px;margin:2px;font-size:18px;">${p.trim()}</span>`).join(' '):'—';

    const html=`<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Bill — ${invNo}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:Arial,sans-serif;font-size:20px;font-weight:900;color:#000;background:#fff;padding:10mm 12mm;}
.bill-header{border-bottom:3px double #000;padding-bottom:8px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:flex-end;}
.shop-name{font-size:40px;font-weight:900;letter-spacing:1px;color:#003366;line-height:1.1;}
.shop-sub{font-size:20px;font-weight:900;color:#000;margin-top:3px;}
.inv-box{text-align:right;}
.inv-box .inv-num{font-size:32px;font-weight:900;color:#003366;border:2px solid #003366;padding:4px 14px;border-radius:4px;display:inline-block;}
.inv-box .inv-label{font-size:18px;font-weight:900;color:#000;text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;}
.info-strip{display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;background:#e4eaf4;border:1px solid #888;border-radius:4px;padding:8px 12px;margin-bottom:10px;}
.info-cell{}
.info-cell .lbl{font-size:18px;text-transform:uppercase;color:#000;letter-spacing:0.5px;font-weight:900;}
.info-cell .val{font-size:22px;font-weight:900;color:#000;margin-top:1px;}
.cust-name{font-size:28px;font-weight:900;color:#003366;}
table.items{width:100%;border-collapse:collapse;margin-bottom:10px;}
table.items thead tr{background:#003366;color:#fff;}
table.items thead th{padding:10px 8px;font-size:20px;font-weight:900;text-align:center;text-transform:uppercase;letter-spacing:0.3px;white-space:nowrap;border:1px solid #003366;}
table.items thead th.l{text-align:left;padding-left:7px;}
table.items tbody tr:nth-child(even){background:#edf1f9;}
table.items tbody td{padding:9px 4px;font-size:17px;font-weight:900;text-align:center;border:1px solid #888;vertical-align:top;}
table.items tfoot tr{background:#003366;color:#fff;}
table.items tfoot td{padding:6px 4px;font-weight:900;font-size:15px;border:1px solid #003366;text-align:center;}
.bottom-section{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:4px;}
.totals-box{border:1px solid #888;border-radius:4px;overflow:hidden;}
.totals-box table{width:100%;border-collapse:collapse;}
.totals-box table td{padding:9px 14px;font-size:22px;font-weight:900;border-bottom:1px solid #999;}
.totals-box table td:last-child{text-align:right;font-family:'Courier New',monospace;font-weight:900;color:#000;}
.totals-box table tr:last-child td{border-bottom:none;}
.totals-box .grand-row td{background:#003366;color:#fff;font-size:26px;font-weight:900;padding:10px 12px;}
.pay-box{border:1px solid #888;border-radius:4px;padding:10px 12px;}
.pay-box .pay-title{font-size:20px;font-weight:900;text-transform:uppercase;color:#000;letter-spacing:0.5px;margin-bottom:6px;border-bottom:1px solid #888;padding-bottom:4px;}
.balance-line{margin-top:10px;padding:8px 12px;background:#003366;color:#fff;border-radius:4px;display:flex;justify-content:space-between;align-items:center;}
.balance-line .b-label{font-size:22px;font-weight:900;text-transform:uppercase;}
.balance-line .b-val{font-size:34px;font-weight:900;font-family:'Courier New',monospace;}
.remarks-bar{background:#fffde7;border:1px solid #f0c040;border-radius:4px;padding:10px 16px;margin-top:8px;font-size:20px;font-weight:900;}
.remarks-bar strong{color:#000;}
.bill-footer{position:fixed;bottom:10mm;left:12mm;right:12mm;border-top:2px solid #003366;padding-top:10px;display:flex;justify-content:space-between;align-items:flex-end;}
.footer-note{font-size:19px;font-weight:900;color:#000;}
.sign-box{text-align:center;font-size:19px;font-weight:900;color:#000;}
.sign-line{border-top:1px solid #000;margin-top:70px;width:160px;}
.stamp-space{width:160px;height:80px;border:1px dashed #aaa;border-radius:4px;margin-top:10px;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:13px;letter-spacing:1px;}
@media print{body{padding:5mm 8mm;}@page{margin:5mm;size:A4;}}
</style>
</head>
<body>
<div class="bill-header">
  <div>
    <div class="shop-name">Sarthi Sports Wear</div>
    <div class="shop-sub">Ph: 0762-0425141 &nbsp;|&nbsp; Mob: 9422107750 &nbsp;|&nbsp; Nagpur, Maharashtra</div>
  </div>
</div>
<div class="info-strip">
  <div class="info-cell" style="grid-column:span 1;">
    <div class="lbl">Customer Name</div>
    <div class="val cust-name">${custName.toUpperCase()}</div>
  </div>
  <div class="info-cell">
    <div class="lbl">Order Date</div>
    <div class="val">${fmtDate(orderDate)}</div>
  </div>
  <div class="info-cell">
    <div class="lbl">Delivery Date</div>
    <div class="val">${fmtDate(delivDate)}</div>
  </div>
  ${advDate?`<div class="info-cell"><div class="lbl">Advance Date</div><div class="val">${fmtDate(advDate)}</div></div>`:''}
</div>
<table class="items">
  <thead>
    <tr>
      <th style="width:28px;">#</th>
      <th class="l" style="width:90px;">Product</th>
      <th class="l" style="width:90px;">Pattern</th>
      <th class="l" style="width:70px;">Fabric</th>
      <th class="l" style="width:80px;">Color</th>
  <th style="width:40px;">Size</th>
      <th style="width:35px;">Qty</th>
      <th style="width:55px;">MRP</th>
      <th style="width:40px;">Dis%</th>
      <th style="width:70px;">Net Rate</th>
      <th style="width:110px;">Amount</th>
     <th class="l" style="width:100px;">Notes</th>
    </tr>
  </thead>
  <tbody>${itemRows}</tbody>
  <tfoot>
    <tr>
     <td colspan="10" style="text-align:right;padding-right:12px;">TOTAL QTY &amp; GROSS</td>
      <td>${grandQty}</td>
      <td style="white-space:nowrap;">${gross}</td>
    </tr>
  </tfoot>
</table>
<div class="bottom-section">
  <div class="totals-box">
    <table>
      <tr><td>Gross Amount</td><td>${gross}</td></tr>
      ${parseFloat(document.getElementById('pc_total_val').textContent.replace(/[₹ +,]/g,''))||0 > 0 ? `<tr><td>Print Charges</td><td>${pcTotal}</td></tr>`:''}
      ${parseFloat(discPct)?`<tr><td>Discount (${discPct}%)</td><td style="color:#000;">${discAmt}</td></tr>`:''}
      ${parseFloat(discFlat)?`<tr><td>Discount Flat</td><td style="color:#000;">${discFlatAmt}</td></tr>`:''}
      ${chargesAmt && chargesAmt!='₹ 0.00'?`<tr><td>GST / Charges</td><td>${chargesAmt}</td></tr>`:''}
      <tr class="grand-row"><td>NET AMOUNT</td><td>${net}</td></tr>
    </table>
  </div>
  <div>
    <div class="pay-box">
      <div class="pay-title">💳 Payment Details</div>
      ${payStr}
  </div>
</div>
${remarks?`<div class="remarks-bar"><strong>📝 Remarks:</strong> ${remarks}</div>`:''}
<table style="width:100%;margin-top:40px;border-top:2px solid #003366;padding-top:10px;">
  <tr>
    <td style="font-size:19px;font-weight:900;color:#000;vertical-align:bottom;">
      Thank you for your order!<br>Goods once sold will not be taken back.
    </td>
    <td style="width:200px;text-align:center;vertical-align:bottom;">
      <div style="height:60px;"></div>
      <div style="border-top:1px solid #000;padding-top:5px;font-size:19px;font-weight:900;">Authorised Signatory</div>
    </td>
  </tr>
</table>
</div>
</body></html>`;
    printViaIframe(html);
}

// ─── Collect Items ────────────────────────────────────────────────────────────
function collectAllItems(){
    const items=[];
    const logo_pc=parseFloat(document.getElementById('pc_logo').value)||0;
    const back_pc=parseFloat(document.getElementById('pc_back').value)||0;
    document.querySelectorAll('#tableBody tr').forEach(tr=>{
        const product=tr.querySelector('select')?.value;if(!product)return;
        const rowIdx=parseInt(tr.dataset.row,10);
        const sizes=[];
        tr.querySelectorAll('.sr-body tr').forEach(sr=>{
            const sz=sr.querySelector('.sr-size')?.value||'';
            const mrp=parseFloat(sr.querySelector('.sr-mrp')?.value)||0;
            const dis=parseFloat(sr.querySelector('.sr-dis')?.value)||0;
            const nr=parseFloat(sr.querySelector('.sr-netrate')?.value)||0;
            const qty=parseFloat(sr.querySelector('.sr-qty')?.value)||0;
            const amt=parseFloat(sr.querySelector('.sr-amount')?.value)||0;
            const pat=sr.querySelector('.sr-pattern')?.value||'';
            const fab=sr.querySelector('.sr-fabric')?.value||'';
            const col=sr.querySelector('.sr-color')?.value||'';
            if(sz||qty>0)sizes.push({size:sz,mrp,dis,netrate:nr,qty,amount:amt,pattern:pat,fabric:fab,color:col});
        });
        const totalQty=sizes.reduce((s,r)=>s+r.qty,0);if(totalQty<=0)return;
        const isTshirt=product==='tshirt';
        items.push({product,pattern:sizes[0]?.pattern||'',fabric:sizes[0]?.fabric||'',color:sizes[0]?.color||'',notes:tr.querySelector('.row-notes')?.value||'',logo_charge:isTshirt?logo_pc:0,back_charge:isTshirt?back_pc:0,print_charges:isTshirt?(logo_pc+back_pc)*totalQty:0,sizes});
    });
    return items;
}
function collectPaymentModes(){
    const parts=[];
    document.querySelectorAll('#paymode-entries .paymode-entry').forEach(row=>{
        const mode=row.querySelector('.paymode-select')?.value;
        const amt=row.querySelector('.paymode-amount')?.value;
        if(mode&&amt)parts.push(`${mode}:${amt}`);
    });
    return parts.join(', ');
}

// ─── Confirm / Save Order ─────────────────────────────────────────────────────
async function confirmOrder(){
    const cust=document.getElementById('customer_name').value.trim();
    if(!cust){await showAlert('Please enter the Customer Name.','Customer Name Required','warn');document.getElementById('customer_name').focus();return;}
    const items=collectAllItems();
    if(!items.length){await showAlert('Please add at least one product with quantity.','No Items Added','warn');return;}
    const grossText=document.getElementById('gross_amt').textContent.replace(/[₹ ,]/g,'');
    const netText=document.getElementById('net_amt').textContent.replace(/[₹ ,]/g,'');
    const form=document.createElement('form');form.method='POST';form.action='';
    const fields={
        customer_name:cust,
        invoice_no:document.getElementById('invoice_no').value,
        order_date:document.getElementById('order_date').value,
        delivery_date:document.getElementById('delivery_date').value,
        advance_date:document.getElementById('advance_date').value,
        bill_type:document.getElementById('bill_type').value,
        bill_status:document.getElementById('bill_status').value,
        remarks:document.getElementById('remarks').value,
        disc_pct:document.getElementById('disc_input').value||'0',
        disc_flat:document.getElementById('disc_flat_input').value||'0',
        extra_charges:(()=>{
            const gp=parseFloat(document.getElementById('charges_input').value)||0;
            const ga=parseFloat(document.getElementById('gross_amt').textContent.replace(/[₹ ,]/g,''))||0;
            return (ga*gp/100).toFixed(2);
        })(),
        gross_amount:grossText,
        net_amount:netText,
        payment_modes:collectPaymentModes(),
        all_items_json:JSON.stringify(items)
    };
    for(const[k,v]of Object.entries(fields)){
        const i=document.createElement('input');i.type='hidden';i.name=k;i.value=v;form.appendChild(i);
    }
    /* ── Notify home.php that this MC got billed ── */
   var _urlp=new URLSearchParams(window.location.search);
    var _mc=_urlp.get('from_mc');
    if(_mc){
        try{ localStorage.setItem('mc_bill_made_'+_mc,'1'); }catch(e){}
        /* Save to DB synchronously before form submits */
        try{
            var xh=new XMLHttpRequest();
            xh.open('POST','mark_bill_made.php',false);
            xh.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xh.send('mc_no='+encodeURIComponent(_mc));
        }catch(e){}
    }
    document.body.appendChild(form);form.submit();
}
   

async function printMasterCopy(){
    let tableRows='';
    const logo_pc=parseFloat(document.getElementById('pc_logo').value)||0;
    const back_pc=parseFloat(document.getElementById('pc_back').value)||0;
    let grandQty=0;
    let srNo=1;
    document.querySelectorAll('#tableBody tr').forEach(tr=>{
        const rowIdx=parseInt(tr.dataset.row,10);
        const srPanel=document.getElementById(`srp-${rowIdx}`);
        const product=tr.querySelector('select')?.value;
        if(!product||!srPanel)return;
        const rows=srPanel.querySelectorAll('.sr-body tr');
        let hasQty=false;
        rows.forEach(r=>{if(parseFloat(r.querySelector('.sr-qty')?.value)||0)hasQty=true;});
        if(!hasQty)return;
        rows.forEach((r,si)=>{
            const sz=r.querySelector('.sr-size')?.value||'';
            const pat=r.querySelector('.sr-pattern')?.value||'';
            const fab=r.querySelector('.sr-fabric')?.value||'';
            const col=r.querySelector('.sr-color')?.value||'';
            const mrp=r.querySelector('.sr-mrp')?.value||'';
            const qty=parseFloat(r.querySelector('.sr-qty')?.value)||0;
            if(!qty)return;
            grandQty+=qty;
            const prodLabel=product.charAt(0).toUpperCase()+product.slice(1).replace(/_/g,' ');
            const printNote=(si===0&&product==='tshirt'&&(logo_pc>0||back_pc>0))?`<br><small style="color:#000;font-size:16px;">Logo ₹${logo_pc}+Back ₹${back_pc}/pc</small>`:'';
            tableRows+=`<tr>
                <td>${si===0?srNo:''}</td>
                <td style="text-align:left;">${si===0?prodLabel+printNote:''}</td>
                <td style="text-align:left;">${pat}</td>
                <td>${sz}</td>
                <td style="text-align:left;">${fab}</td>
                <td style="text-align:left;">${col}</td>
                <td>${mrp?'₹'+parseFloat(mrp).toFixed(2):''}</td>
                <td><strong>${qty}</strong></td>
            </tr>`;
        });
        srNo++;
    });
    if(!tableRows){await showAlert('No items to print.','No Items','warn');return;}
    const custName=document.getElementById('customer_name').value||'—';
    const invNo=document.getElementById('invoice_no').value||'—';
    const orderDate=document.getElementById('order_date').value;
    const delivDate=document.getElementById('delivery_date').value;
    const advDate=document.getElementById('advance_date').value;
    const billStatus=document.getElementById('bill_status').value;
    const remarks=document.getElementById('remarks').value;
    function fmtDate(d){if(!d)return '—';const p=d.split('-');return p.length===3?`${p[2]}/${p[1]}/${p[0]}`:d;}

    const html=`<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Master Copy — ${invNo}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:Arial,sans-serif;font-size:19px;font-weight:900;color:#000;background:#fff;padding:10mm 12mm;}
.bill-header{border-bottom:3px double #000;padding-bottom:8px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:flex-end;}
.shop-name{font-size:36px;font-weight:900;letter-spacing:1px;color:#003366;}
.shop-sub{font-size:19px;font-weight:900;color:#000;margin-top:3px;}
.mc-badge{background:#003366;color:#fff;font-size:22px;font-weight:900;padding:5px 16px;border-radius:4px;letter-spacing:1px;}
.info-strip{display:grid;grid-template-columns:repeat(4,1fr);gap:6px;background:#e4eaf4;border:1px solid #888;border-radius:4px;padding:8px 12px;margin-bottom:10px;}
.info-cell .lbl{font-size:18px;text-transform:uppercase;color:#000;font-weight:900;}
.info-cell .val{font-size:22px;font-weight:900;color:#000;margin-top:1px;}
.cust-val{font-size:28px;font-weight:900;color:#003366;}
table.mc{width:100%;border-collapse:collapse;margin-bottom:10px;}
table.mc thead tr{background:#003366;color:#fff;}
table.mc thead th{padding:11px 9px;font-size:20px;font-weight:900;text-align:center;text-transform:uppercase;border:1px solid #003366;white-space:nowrap;}
table.mc thead th.l{text-align:left;padding-left:8px;}
table.mc tbody tr:nth-child(even){background:#edf1f9;}
table.mc tbody td{padding:10px 8px;font-size:20px;font-weight:900;border:1px solid #888;text-align:center;vertical-align:top;}
table.mc tfoot td{background:#003366;color:#fff;font-weight:900;font-size:23px;padding:11px 9px;border:1px solid #003366;text-align:center;}
.remarks-bar{background:#fffde7;border:1px solid #f0c040;border-radius:4px;padding:6px 12px;margin-top:8px;font-size:16px;}

.footer-note{font-size:18px;font-weight:900;color:#000;}
.sign-box{text-align:center;font-size:18px;font-weight:900;color:#000;}
.sign-line{border-top:1px solid #000;margin-top:70px;width:160px;}
.stamp-space{width:160px;height:80px;border:1px dashed #aaa;border-radius:4px;margin-top:10px;}
@media print{body{padding:5mm 8mm;}@page{margin:5mm;size:A4;}}
.no-print{
display:none !important;
}
}
</style>
</head>
<body>
<div style="text-align:center;font-size:28px;font-weight:900;letter-spacing:2px;color:#003366;border-bottom:3px double #000;padding-bottom:6px;margin-bottom:6px;">⚙ MASTER COPY</div>
<div class="bill-header">
  <div>
    <div class="shop-name">Sarthi Sports Wear</div>
    <div class="shop-sub">Ph: 0762-0425141 &nbsp;|&nbsp; Mob: 9422107750 &nbsp;|&nbsp; Nagpur, Maharashtra</div>
  </div>
</div>
<div class="info-strip">
  <div class="info-cell" style="grid-column:span 2;">
    <div class="lbl">Customer Name</div>
    <div class="val cust-val">${custName.toUpperCase()}</div>
  </div>
  <div class="info-cell">
    <div class="lbl">Order Date</div>
    <div class="val">${fmtDate(orderDate)}</div>
  </div>
  <div class="info-cell">
    <div class="lbl">Delivery Date</div>
    <div class="val">${fmtDate(delivDate)}</div>
  </div>
  ${advDate?`<div class="info-cell"><div class="lbl">Advance Date</div><div class="val">${fmtDate(advDate)}</div></div>`:''}
</div>
<table class="mc">
  <thead>
    <tr>
      <th style="width:28px;">#</th>
      <th class="l" style="width:100px;">Product</th>
      <th class="l" style="width:100px;">Pattern</th>
      <th style="width:44px;">Size</th>
      <th class="l" style="width:80px;">Fabric</th>
      <th class="l" style="width:90px;">Color</th>
      <th style="width:65px;">MRP</th>
      <th style="width:44px;">Qty</th>
    </tr>
  </thead>
  <tbody>${tableRows}</tbody>
  <tfoot>
    <tr>
      <td colspan="7" style="text-align:right;padding-right:12px;">TOTAL QTY</td>
      <td>${grandQty}</td>
    </tr>
  </tfoot>
</table>
${remarks?`<div class="remarks-bar"><strong>📝 Remarks:</strong> ${remarks}</div>`:''}
 
 <table style="width:100%;margin-top:40px;">
  <tr>
    <td style="width:60%;"></td>
    <td style="width:40%;text-align:center;">
      <div style="height:50px;"></div>
      <div style="border-top:2px solid #000;padding-top:5px;font-size:18px;font-weight:900;font-family:Arial,sans-serif;">Authorised Signatory</div>
    </td>
  </tr>
</table>
</body></html>`;
    printViaIframe(html);
}

// ─── WhatsApp / PDF ───────────────────────────────────────────────────────────
async function sendWhatsApp(){
    const cust=document.getElementById('customer_name').value.trim();
    if(!cust){await showAlert('Please enter Customer Name first.','Customer Required','warn');return;}
    const items=collectAllItems();
    if(!items.length){await showAlert('No items to include.','No Items','warn');return;}

    const phone=await askPhoneNumber();
    if(phone===null)return;

    const waBtn=document.getElementById('waBtn');
    const origHTML=waBtn.innerHTML;
    waBtn.innerHTML='<span class="wa-spin">⏳</span>';
    waBtn.disabled=true;
    const grossText=document.getElementById('gross_amt').textContent.replace(/[₹ ,]/g,'');
    const netText=document.getElementById('net_amt').textContent.replace(/[₹ ,]/g,'');
    const balText=document.getElementById('balance_amt').textContent.replace(/[₹ ,]/g,'');
    const fd=new FormData();
    fd.append('invoice_no',document.getElementById('invoice_no').value);
    fd.append('customer_name',cust);
    fd.append('order_date',document.getElementById('order_date').value);
    fd.append('delivery_date',document.getElementById('delivery_date').value);
    fd.append('advance_date',document.getElementById('advance_date').value);
    fd.append('bill_type',document.getElementById('bill_type').value);
    fd.append('bill_status',document.getElementById('bill_status').value);
    fd.append('remarks',document.getElementById('remarks').value);
    fd.append('disc_pct',document.getElementById('disc_input').value||'0');
    fd.append('disc_flat',document.getElementById('disc_flat_input').value||'0');
    fd.append('extra_charges',(()=>{
        const gp=parseFloat(document.getElementById('charges_input').value)||0;
        const ga=parseFloat(document.getElementById('gross_amt').textContent.replace(/[₹ ,]/g,''))||0;
        return (ga*gp/100).toFixed(2);
    })());
    fd.append('gross_amount',grossText);
    fd.append('net_amount',netText);
    fd.append('balance_due',balText);
    fd.append('payment_modes',collectPaymentModes());
    fd.append('all_items_json',JSON.stringify(items));
    try{
        const response=await fetch('?action=generate_pdf',{method:'POST',body:fd});
        const contentType=response.headers.get('content-type')||'';
        if(contentType.includes('application/octet-stream')||contentType.includes('application/pdf')){
            const blob=await response.blob();
            const url=window.URL.createObjectURL(blob);
            const a=document.createElement('a');
            a.href=url;
            a.download='Bill_'+document.getElementById('invoice_no').value+'_'+cust.replace(/\s+/g,'_').replace(/[^a-zA-Z0-9_]/g,'')+'.pdf';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();

            const cleanPh=phone.replace(/\D/g,'');
            setTimeout(()=>{
                window.open(cleanPh.length>4?`https://wa.me/${cleanPh}`:`https://wa.me/`,'_blank');
            },500);

            await showAlert('PDF downloaded! Attach it manually on WhatsApp.','Bill Ready ✅','info');
        }else{
            const result=await response.json();
            if(!result.success)throw new Error(result.error||'PDF generation failed');
        }
    }catch(err){
        await showAlert('Could not generate PDF: '+err.message+'\n\nMake sure TCPDF is installed.\nRun: composer require tecnickcom/tcpdf','PDF Error','error');
    }finally{
        waBtn.innerHTML=origHTML;
        waBtn.disabled=false;
    }
}

function printViaIframe(htmlContent){
    const old=document.getElementById('__printFrame');if(old)old.remove();
    const iframe=document.createElement('iframe');
    iframe.id='__printFrame';
    iframe.style.cssText='position:fixed;top:-9999px;left:-9999px;width:0;height:0;border:none;';
    document.body.appendChild(iframe);
    const doc=iframe.contentWindow.document;
    doc.open();doc.write(htmlContent);doc.close();
    iframe.onload=()=>{iframe.contentWindow.focus();iframe.contentWindow.print();setTimeout(()=>iframe.remove(),1000);};
}
</script>
</body>
</html>