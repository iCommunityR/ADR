<?php
function url(string $path=''): string { global $config; return $config['app']['base_url'] . ($path ? '/' . ltrim($path,'/') : ''); }
function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function db(): PDO { static $pdo; global $config; if($pdo instanceof PDO) return $pdo; $d=$config['db']; $pdo=new PDO("mysql:host={$d['host']};port={$d['port']};dbname={$d['name']};charset={$d['charset']}",$d['user'],$d['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]); return $pdo; }
function db_or_null(): ?PDO { try{return db();}catch(Throwable){return null;} }
function installed(): bool { $pdo=db_or_null(); if(!$pdo)return false; try{return (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn()>0;}catch(Throwable){return false;} }
function go(string $path): never { header('Location: '.(str_starts_with($path,'http')?$path:url($path))); exit; }
function csrf(): string { if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf()).'">'; }
function check_csrf(): void { if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(419);exit(e(t('session_expired')));} }
function flash(string $type,string $message): void { $_SESSION['flash'][]=[$type,$message]; }
function flashes(): array { $f=$_SESSION['flash']??[]; unset($_SESSION['flash']); return $f; }
function admin_user(): ?array { return $_SESSION['admin']??null; }
function require_admin(): void { if(!admin_user())go('admin.php?view=login'); }
function login_admin(string $email,string $password): bool { $pdo=db_or_null(); if(!$pdo)return false; $s=$pdo->prepare('SELECT * FROM users WHERE email=? AND is_active=1 LIMIT 1');$s->execute([strtolower(trim($email))]);$u=$s->fetch();if(!$u||!password_verify($password,$u['password_hash']))return false;unset($u['password_hash']);session_regenerate_id(true);$_SESSION['admin']=$u;activity('login','Administrator signed in');return true; }
function activity(string $action,string $details=''): void { $pdo=db_or_null();if(!$pdo)return;try{$u=admin_user();$s=$pdo->prepare('INSERT INTO activity_logs(user_id,action,details,ip_address) VALUES(?,?,?,?)');$s->execute([$u['id']??null,$action,$details,$_SERVER['REMOTE_ADDR']??null]);}catch(Throwable){} }
function country_by_code(string $code): ?array { foreach(country_data() as $c)if($c[0]===strtoupper($code))return ['code'=>$c[0],'name'=>$c[1],'region'=>$c[2],'common_law'=>in_array($c[0],common_law_codes(),true)];return null; }
function excerpt(?string $t,int $n=175): string { $t=trim(strip_tags((string)$t));$len=function_exists('mb_strlen')?mb_strlen($t):strlen($t);if($len<=$n)return $t;$cut=function_exists('mb_substr')?mb_substr($t,0,$n-1):substr($t,0,$n-1);return rtrim($cut).'…'; }
function icon(string $n): string { $p=['search'=>'<path d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"/>','arrow'=>'<path d="M5 12h14M13 6l6 6-6 6"/>','globe'=>'<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/>','book'=>'<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5v14Zm0 0A2.5 2.5 0 0 0 6.5 22H20v-5"/>','scale'=>'<path d="M12 3v18M5 7h14M5 7l-3 6h6L5 7Zm14 0-3 6h6l-3-6ZM8 21h8"/>','building'=>'<path d="M3 21h18M5 21V9l7-5 7 5v12M9 21v-6h6v6"/>','file'=>'<path d="M6 2h8l4 4v16H6V2Zm8 0v5h5M9 12h6M9 16h6"/>','menu'=>'<path d="M4 6h16M4 12h16M4 18h16"/>','upload'=>'<path d="M12 16V4M7 9l5-5 5 5M4 20h16"/>'];return '<svg viewBox="0 0 24 24" aria-hidden="true">'.($p[$n]??$p['file']).'</svg>'; }

function external_url(?string $value): string {
  $value=trim((string)$value);
  if($value==='')return '';
  if(!filter_var($value,FILTER_VALIDATE_URL))throw new RuntimeException(t('valid_source_url'));
  $scheme=strtolower((string)parse_url($value,PHP_URL_SCHEME));
  if(!in_array($scheme,['http','https'],true))throw new RuntimeException(t('http_only'));
  return $value;
}
function valid_upload_signature(string $path,string $ext): bool {
  $head=(string)file_get_contents($path,false,null,0,16);
  if($ext==='pdf')return str_starts_with($head,'%PDF-');
  if(in_array($ext,['doc','xls','ppt'],true))return str_starts_with($head,"\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1");
  if(in_array($ext,['docx','xlsx','pptx'],true)){
    if(!str_starts_with($head,'PK'))return false;
    if(class_exists('ZipArchive')){
      $zip=new ZipArchive();
      if($zip->open($path)!==true)return false;
      $required=['docx'=>'word/document.xml','xlsx'=>'xl/workbook.xml','pptx'=>'ppt/presentation.xml'][$ext];
      $ok=$zip->locateName('[Content_Types].xml')!==false&&$zip->locateName($required)!==false;
      $zip->close();return $ok;
    }
    return true;
  }
  if(in_array($ext,['txt','csv'],true))return !str_contains($head,"\0");
  return false;
}
function upload_file(string $field,?string $old=null,bool $deleteOld=true): ?array { global $config;if(empty($_FILES[$field])||$_FILES[$field]['error']===UPLOAD_ERR_NO_FILE)return null;$f=$_FILES[$field];if($f['error']!==UPLOAD_ERR_OK)throw new RuntimeException('Upload failed.');if($f['size']>$config['upload']['max_bytes'])throw new RuntimeException('File exceeds 10 MB.');$original=basename($f['name']);$ext=strtolower(pathinfo($original,PATHINFO_EXTENSION));if(!in_array($ext,$config['upload']['extensions'],true))throw new RuntimeException('Unsupported file type.');if(!valid_upload_signature($f['tmp_name'],$ext))throw new RuntimeException('The uploaded file signature does not match its extension.');$mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name'])?:'application/octet-stream';if(!in_array($mime,$config['upload']['mimes'],true))throw new RuntimeException('File content is not an allowed document type.');$dir=$config['upload']['path'].'/'.date('Y/m');if(!is_dir($dir))mkdir($dir,0750,true);$name=bin2hex(random_bytes(20)).'.'.$ext;if(!move_uploaded_file($f['tmp_name'],$dir.'/'.$name))throw new RuntimeException('Could not save file.');if($old&&$deleteOld){$p=$config['upload']['path'].'/'.$old;if(is_file($p))@unlink($p);}return ['path'=>date('Y/m').'/'.$name,'name'=>$original,'mime'=>$mime,'size'=>(int)$f['size']]; }
function format_bytes(int $b): string { return $b<1024?$b.' B':($b<1048576?number_format($b/1024,1).' KB':number_format($b/1048576,1).' MB'); }

function content_languages(): array {
  return ['en'=>'English','fr'=>'French','ar'=>'Arabic','pt'=>'Portuguese','sw'=>'Swahili'];
}

function legal_statuses(): array {
  return ['current'=>'Current','amended'=>'Amended','repealed'=>'Repealed','superseded'=>'Superseded','draft'=>'Draft'];
}

function legal_status_label(string $status): string {
  $key='status_'.$status;
  $translated=t($key);
  return $translated===$key?(legal_statuses()[$status]??ucfirst($status)):$translated;
}

function valid_date_or_null(?string $date): ?string {
  $date=trim((string)$date);
  if($date==='')return null;
  $parsed=DateTimeImmutable::createFromFormat('Y-m-d',$date);
  if(!$parsed||$parsed->format('Y-m-d')!==$date)throw new RuntimeException('Enter dates in YYYY-MM-DD format.');
  return $date;
}

function research_user(): ?array { return $_SESSION['research_user']??null; }
function require_research(): void { if(!research_user()){$_SESSION['research_return']=$_SERVER['REQUEST_URI']??url('index.php?page=research');go('index.php?page=research-login');} }
function research_logout(): void { unset($_SESSION['research_user']);session_regenerate_id(true); }
function research_login(string $email,string $password): bool {
  $pdo=db_or_null();if(!$pdo)return false;
  $s=$pdo->prepare('SELECT * FROM research_users WHERE email=? AND is_active=1 LIMIT 1');
  $s->execute([strtolower(trim($email))]);$user=$s->fetch();
  if(!$user||!password_verify($password,$user['password_hash']))return false;
  unset($user['password_hash']);session_regenerate_id(true);$_SESSION['research_user']=$user;
  $pdo->prepare('UPDATE research_users SET last_login_at=NOW() WHERE id=?')->execute([$user['id']]);
  return true;
}
function research_register(string $name,string $email,string $password,string $organization=''): array {
  $name=trim($name);$email=strtolower(trim($email));$organization=trim($organization);
  if($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL))throw new RuntimeException(t('registration_details_required'));
  if(strlen($password)<10)throw new RuntimeException(t('password_minimum'));
  $pdo=db();
  $s=$pdo->prepare('SELECT id FROM research_users WHERE email=?');$s->execute([$email]);
  if($s->fetchColumn())throw new RuntimeException(t('account_email_exists'));
  $hash=password_hash($password,PASSWORD_DEFAULT);
  $s=$pdo->prepare('INSERT INTO research_users(name,email,password_hash,organization) VALUES(?,?,?,?)');
  $s->execute([$name,$email,$hash,$organization?:null]);
  $id=(int)$pdo->lastInsertId();
  $pdo->prepare('INSERT INTO research_folders(research_user_id,name,description) VALUES(?,?,?)')->execute([$id,t('default_folder_name'),t('default_folder_description')]);
  try{$pdo->prepare("UPDATE institutional_members SET research_user_id=?,joined_at=NOW(),status=IF(status='invited','active',status) WHERE email=? AND research_user_id IS NULL")->execute([$id,$email]);}catch(Throwable){}
  return ['id'=>$id,'name'=>$name,'email'=>$email,'organization'=>$organization,'is_active'=>1];
}

function user_folders(?int $userId=null): array {
  $userId=$userId??(int)(research_user()['id']??0);if(!$userId)return [];
  $s=db()->prepare('SELECT f.*,COUNT(i.id) item_count FROM research_folders f LEFT JOIN research_folder_items i ON i.folder_id=f.id WHERE f.research_user_id=? GROUP BY f.id ORDER BY f.updated_at DESC,f.name');
  $s->execute([$userId]);return $s->fetchAll();
}

function owned_folder(int $folderId): ?array {
  $user=research_user();if(!$user)return null;
  $s=db()->prepare('SELECT * FROM research_folders WHERE id=? AND research_user_id=?');$s->execute([$folderId,$user['id']]);
  return $s->fetch()?:null;
}

function folder_items(int $folderId): array {
  $folder=owned_folder($folderId);if(!$folder)return [];
  $s=db()->prepare("SELECT rfi.*,
    CASE rfi.entity_type WHEN 'document' THEN d.title WHEN 'case' THEN ca.case_name WHEN 'institution' THEN i.name END item_title,
    CASE rfi.entity_type WHEN 'document' THEN COALESCE(c1.name,UPPER(d.regime_key),'Regional') WHEN 'case' THEN c2.name WHEN 'institution' THEN c3.name END item_scope,
    CASE rfi.entity_type WHEN 'document' THEN COALESCE(d.year,YEAR(d.effective_date)) WHEN 'case' THEN ca.year ELSE NULL END item_year,
    d.document_type,d.document_number,d.effective_date,d.legal_status,d.source_url document_source,
    ca.citation,ca.court,ca.decision_date,ca.primary_subject,ca.source_url case_source,
    i.website institution_website
    FROM research_folder_items rfi
    LEFT JOIN documents d ON rfi.entity_type='document' AND d.id=rfi.entity_id
    LEFT JOIN countries c1 ON c1.id=d.country_id
    LEFT JOIN cases ca ON rfi.entity_type='case' AND ca.id=rfi.entity_id
    LEFT JOIN countries c2 ON c2.id=ca.country_id
    LEFT JOIN institutions i ON rfi.entity_type='institution' AND i.id=rfi.entity_id
    LEFT JOIN countries c3 ON c3.id=i.country_id
    WHERE rfi.folder_id=? ORDER BY rfi.created_at DESC");
  $s->execute([$folderId]);return array_values(array_filter($s->fetchAll(),fn($r)=>!empty($r['item_title'])));
}

function entity_exists(string $type,int $id): bool {
  $tables=['document'=>'documents','case'=>'cases','institution'=>'institutions'];
  if(!isset($tables[$type]))return false;
  $s=db()->prepare('SELECT COUNT(*) FROM '.$tables[$type].' WHERE id=? AND is_published=1');$s->execute([$id]);
  return (int)$s->fetchColumn()>0;
}

function safe_return_url(?string $value,string $fallback='index.php?page=research'): string {
  $value=trim((string)$value);
  if($value===''||str_contains($value,"\r")||str_contains($value,"\n")||str_contains($value,'..'))return url($fallback);
  $parts=parse_url($value);
  if(isset($parts['scheme'])||isset($parts['host']))return url($fallback);
  if(str_starts_with($value,'/'))return $value;
  return url(ltrim($value,'/'));
}

function platform_schema_ready(?PDO $pdo=null): bool {
  $pdo=$pdo??db_or_null();if(!$pdo)return false;
  try{
    $s=$pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='documents' AND COLUMN_NAME='legal_status'");$s->execute();
    if((int)$s->fetchColumn()===0)return false;
    $s=$pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME IN ('document_versions','research_users','research_folders','institutional_subscriptions','institutional_members')");$s->execute();
    return (int)$s->fetchColumn()===5;
  }catch(Throwable){return false;}
}

function snapshot_document_version(PDO $pdo,array $document,string $changeNote=''): void {
  if(empty($document['id']))return;
  $s=$pdo->prepare('SELECT COALESCE(MAX(revision_no),0)+1 FROM document_versions WHERE document_id=?');$s->execute([$document['id']]);$revision=(int)$s->fetchColumn();
  $sql='INSERT INTO document_versions(document_id,revision_no,country_id,regime_key,section_key,document_type,language_code,document_number,title,summary,year,version_label,legal_status,effective_date,repeal_date,last_verified_at,verification_source,verification_notes,supersedes_document_id,repealed_by_document_id,source_url,keywords,file_path,original_filename,mime_type,file_size,is_published,published_at,change_note,changed_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
  $pdo->prepare($sql)->execute([
    $document['id'],$revision,$document['country_id']??null,$document['regime_key']??null,$document['section_key']??'national-legislation',$document['document_type'],$document['language_code']??'en',$document['document_number']??null,$document['title'],$document['summary']??null,$document['year']??null,$document['version_label']??null,$document['legal_status']??'current',$document['effective_date']??null,$document['repeal_date']??null,$document['last_verified_at']??null,$document['verification_source']??null,$document['verification_notes']??null,$document['supersedes_document_id']??null,$document['repealed_by_document_id']??null,$document['source_url']??null,$document['keywords']??null,$document['file_path']??null,$document['original_filename']??null,$document['mime_type']??null,$document['file_size']??null,(int)($document['is_published']??0),$document['published_at']??null,$changeNote?:'Saved before update',admin_user()['id']??null
  ]);
}

function citation_for_item(array $item): string {
  $scope=trim((string)($item['item_scope']??''));$title=trim((string)($item['item_title']??''));$year=(string)($item['item_year']??'n.d.');
  if(($item['entity_type']??'')==='case'){
    $citation=trim((string)($item['citation']??''));$court=trim((string)($item['court']??''));
    return trim($title.($citation?' ('.$citation.')':'').($court?', '.$court:'').($year?', '.$year:'').'.');
  }
  if(($item['entity_type']??'')==='institution')return trim($title.($scope?', '.$scope:'').', African Disputes Resolution institutional directory.');
  $number=trim((string)($item['document_number']??''));$typeKey=trim((string)($item['document_type']??''));$type=document_types()[$typeKey]??$typeKey;
  return trim($scope.($scope?', ':'').$title.($number?' ('.$number.')':'').($type?', '.$type:'').($year?', '.$year:'').'.');
}

function citation_key(array $item): string {
  $base=strtolower(preg_replace('/[^a-zA-Z0-9]+/','-',(string)($item['item_title']??'adr'))??'adr');$base=trim(substr($base,0,40),'-');
  return ($base!==''?$base:'adr').'-'.((int)($item['entity_id']??0));
}

function document_versions(int $documentId,bool $publishedOnly=false): array {
  $sql='SELECT v.*,u.name changed_by_name FROM document_versions v LEFT JOIN users u ON u.id=v.changed_by WHERE v.document_id=?';
  if($publishedOnly)$sql.=' AND v.is_published=1';
  $sql.=' ORDER BY v.revision_no DESC';$s=db()->prepare($sql);$s->execute([$documentId]);return $s->fetchAll();
}


function institutional_memberships(?int $userId=null): array {
  $userId=$userId??(int)(research_user()['id']??0);if(!$userId)return [];
  try{$s=db()->prepare("SELECT m.*,s.institution_name,s.status subscription_status,p.name plan_name FROM institutional_members m JOIN institutional_subscriptions s ON s.id=m.subscription_id LEFT JOIN subscription_plans p ON p.id=s.plan_id WHERE m.research_user_id=? AND m.status='active' AND s.status IN ('trial','active') AND (s.end_date IS NULL OR s.end_date>=CURRENT_DATE) ORDER BY s.institution_name");$s->execute([$userId]);return $s->fetchAll();}catch(Throwable){return [];}
}
