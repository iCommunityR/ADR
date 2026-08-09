<?php
require __DIR__.'/includes/bootstrap.php';
$ok='';$error='';$already=installed();
function run_sql_script(PDO $pdo,string $file): void {
  $sql=(string)file_get_contents($file);
  $sql=preg_replace('/^\s*--.*$/m','',$sql)??$sql;
  foreach(preg_split('/;\s*(?:\r?\n|$)/',$sql)?:[] as $statement){
    $statement=trim($statement);
    if($statement!=='')$pdo->exec($statement);
  }
}
if($_SERVER['REQUEST_METHOD']==='POST'){
  check_csrf();
  if($already){http_response_code(403);$error='The platform is already installed. Remove or rename install.php.';}
  else try{
    $pdo=db();
    run_sql_script($pdo,__DIR__.'/database/schema.sql');
    run_sql_script($pdo,__DIR__.'/database/seed.sql');
    $name=trim($_POST['name']??'Platform Administrator');
    $email=strtolower(trim($_POST['email']??''));
    $pass=$_POST['password']??'';
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pass)<12)throw new RuntimeException('Use a valid email and a password of at least 12 characters.');
    $hash=password_hash($pass,PASSWORD_DEFAULT);
    $s=$pdo->prepare("INSERT INTO users(name,email,password_hash,role,is_active) VALUES(?,?,?,'super_admin',1)");
    $s->execute([$name,$email,$hash]);
    $ok='Installation complete. Delete or rename install.php before production use.';$already=true;
  }catch(Throwable $e){$error=$e->getMessage();}
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Install | African Disputes Resolution</title><link rel="icon" type="image/svg+xml" href="<?=url('assets/favicon.svg')?>"><link rel="icon" href="<?=url('favicon.ico')?>" sizes="any"><link rel="icon" type="image/png" href="<?=url('assets/favicon-128.png')?>"><link rel="stylesheet" href="<?=url('assets/style.css')?>"></head><body><main><section class="page-hero"><div class="container"><span class="eyebrow">One-time setup</span><h1>Install African Disputes Resolution</h1><p>Create the complete database schema, load all 54 countries and institutional plans, and create the first administrator.</p></div></section><section class="section"><div class="narrow"><?php if($ok):?><div class="flash success"><div><?=e($ok)?> <a href="<?=url('admin.php')?>">Open admin login.</a></div></div><?php endif;?><?php if($error):?><div class="flash error"><div><?=e($error)?></div></div><?php endif;?><?php if($already&&!$ok):?><div class="form-card"><h2>Already installed</h2><p>The database contains an administrator account. For security, remove or rename <code>install.php</code>.</p><a class="button dark-btn" href="<?=url('admin.php')?>">Open admin login</a></div><?php elseif(!$already):?><form class="form-card" method="post"><?=csrf_field()?><h2>Administrator account</h2><p>Confirm the database settings in <code>config/config.php</code> before continuing.</p><label><span>Name</span><input name="name" value="Platform Administrator" required></label><label><span>Email</span><input type="email" name="email" value="admin@africaadrlaw.org" required></label><label><span>Password</span><input type="password" name="password" minlength="12" required><small>At least 12 characters.</small></label><button>Install platform</button></form><?php endif;?></div></section></main></body></html>
