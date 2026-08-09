<?php
require __DIR__.'/includes/bootstrap.php';
if(!research_user()){$_SESSION['research_return']=$_SERVER['REQUEST_URI']??'index.php?page=research';go('index.php?page=research-login');}
$folderId=(int)($_GET['folder']??0);$format=strtolower(trim($_GET['format']??'csv'));$folder=owned_folder($folderId);
if(!$folder){http_response_code(404);exit('Research folder not found.');}
$items=folder_items($folderId);$safe=preg_replace('/[^A-Za-z0-9_-]+/','-',strtolower($folder['name']))?:'research-folder';
$csvSafe=static function(mixed $value): string {$value=(string)$value;return preg_match('/^[=+\-@]/u',$value)?"'".$value:$value;};

if($format==='csv'){
  header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="'.$safe.'-citations.csv"');header('X-Content-Type-Options: nosniff');
  $out=fopen('php://output','wb');fwrite($out,"\xEF\xBB\xBF");fputcsv($out,['Type','Title','Scope','Year','Citation','Research note','Source URL']);
  foreach($items as $item){$source=$item['entity_type']==='document'?($item['document_source']??''):($item['entity_type']==='case'?($item['case_source']??''):($item['institution_website']??''));fputcsv($out,array_map($csvSafe,[$item['entity_type'],$item['item_title'],$item['item_scope'],$item['item_year'],citation_for_item($item),$item['note'],$source]));}
  fclose($out);exit;
}

if($format==='ris'){
  header('Content-Type: application/x-research-info-systems; charset=utf-8');header('Content-Disposition: attachment; filename="'.$safe.'-citations.ris"');header('X-Content-Type-Options: nosniff');
  foreach($items as $item){$type=$item['entity_type']==='case'?'CASE':($item['entity_type']==='institution'?'ELEC':'GEN');$source=$item['entity_type']==='document'?($item['document_source']??''):($item['entity_type']==='case'?($item['case_source']??''):($item['institution_website']??''));echo "TY  - {$type}\r\n";echo 'TI  - '.str_replace(["\r","\n"],' ',$item['item_title'])."\r\n";if($item['item_scope'])echo 'CY  - '.str_replace(["\r","\n"],' ',$item['item_scope'])."\r\n";if($item['item_year'])echo 'PY  - '.$item['item_year']."\r\n";if(!empty($item['citation']))echo 'M1  - '.str_replace(["\r","\n"],' ',$item['citation'])."\r\n";if($source)echo 'UR  - '.$source."\r\n";if($item['note'])echo 'N1  - '.str_replace(["\r","\n"],' ',$item['note'])."\r\n";echo "PB  - African Disputes Resolution\r\nER  - \r\n\r\n";}
  exit;
}

if($format==='bibtex'){
  header('Content-Type: application/x-bibtex; charset=utf-8');header('Content-Disposition: attachment; filename="'.$safe.'-citations.bib"');header('X-Content-Type-Options: nosniff');
  $escape=fn($value)=>str_replace(['\\','{','}'],['\\\\','\\{','\\}'],str_replace(["\r","\n"],' ',(string)$value));
  foreach($items as $item){$source=$item['entity_type']==='document'?($item['document_source']??''):($item['entity_type']==='case'?($item['case_source']??''):($item['institution_website']??''));echo '@misc{'.citation_key($item).",\n";echo '  title = {'.$escape($item['item_title'])."},\n";echo '  author = {{African Disputes Resolution}},'."\n";if($item['item_year'])echo '  year = {'.$escape($item['item_year'])."},\n";if($item['item_scope'])echo '  address = {'.$escape($item['item_scope'])."},\n";if($source)echo '  url = {'.$escape($source)."},\n";echo '  note = {'.$escape(citation_for_item($item))."}\n}\n\n";}
  exit;
}

http_response_code(400);exit('Unsupported citation format.');
