<?php
require __DIR__.'/includes/bootstrap.php';

$page=$_GET['page']??'home';
$allowed=['home','regimes','countries','country','cases','case','document','institutions','search','about','subscribe','terms','privacy','research-login','research-register','research','folder','research-logout','institutional-subscriptions'];
if(!in_array($page,$allowed,true)){$page='home';http_response_code(404);}

if($page==='research-logout'){
  research_logout();
  flash('success',t('sign_out'));
  go('index.php');
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  check_csrf();
  $action=$_POST['action']??'';
  try{
    if($action==='newsletter-subscribe'){
      $email=strtolower(trim($_POST['email']??''));$name=trim($_POST['name']??'');
      if(!filter_var($email,FILTER_VALIDATE_EMAIL))throw new RuntimeException(t('invalid_email'));
      $pdo=db_or_null();
      if($pdo&&installed()&&platform_schema_ready($pdo)){
        $s=$pdo->prepare("INSERT INTO subscriptions(email,name,status,subscribed_at) VALUES(?,?,'active',NOW()) ON DUPLICATE KEY UPDATE name=VALUES(name),status='active',unsubscribed_at=NULL");
        $s->execute([$email,$name]);
      }
      flash('success',t('subscribed_success'));
      go('index.php?page=subscribe');
    }
    if($action==='research-login'){
      if(!research_login($_POST['email']??'',$_POST['password']??''))throw new RuntimeException(t('invalid_credentials'));
      $return=$_SESSION['research_return']??'index.php?page=research';unset($_SESSION['research_return']);
      header('Location: '.safe_return_url($return));exit;
    }
    if($action==='research-register'){
      $password=(string)($_POST['password']??'');
      if($password!==($_POST['confirm_password']??''))throw new RuntimeException(t('passwords_mismatch'));
      $user=research_register($_POST['name']??'',$_POST['email']??'',$password,$_POST['organization']??'');
      $_SESSION['research_user']=$user;
      flash('success',t('account_created'));
      go('index.php?page=research');
    }
    if($action==='create-folder'){
      require_research();$name=trim($_POST['name']??'');$description=trim($_POST['description']??'');
      if($name==='')throw new RuntimeException(t('folder_name_required'));
      $s=db()->prepare('INSERT INTO research_folders(research_user_id,name,description) VALUES(?,?,?)');
      $s->execute([research_user()['id'],$name,$description?:null]);
      flash('success',t('folder_created'));go('index.php?page=research');
    }
    if($action==='save-item'){
      require_research();$folderId=(int)($_POST['folder_id']??0);$type=(string)($_POST['entity_type']??'');$entityId=(int)($_POST['entity_id']??0);$note=trim($_POST['note']??'');
      if(!owned_folder($folderId)||!entity_exists($type,$entityId))throw new RuntimeException(t('item_unavailable'));
      $s=db()->prepare('INSERT INTO research_folder_items(folder_id,entity_type,entity_id,note) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE note=VALUES(note),created_at=NOW()');
      $s->execute([$folderId,$type,$entityId,$note?:null]);
      db()->prepare('UPDATE research_folders SET updated_at=NOW() WHERE id=?')->execute([$folderId]);
      flash('success',t('saved'));
      header('Location: '.safe_return_url($_POST['return_to']??''));exit;
    }
    if($action==='remove-item'){
      require_research();$folderId=(int)($_POST['folder_id']??0);$itemId=(int)($_POST['item_id']??0);
      if(!owned_folder($folderId))throw new RuntimeException(t('folder_not_found'));
      db()->prepare('DELETE FROM research_folder_items WHERE id=? AND folder_id=?')->execute([$itemId,$folderId]);
      flash('success',t('item_removed'));go('index.php?page=folder&id='.$folderId);
    }
    if($action==='delete-folder'){
      require_research();$folderId=(int)($_POST['folder_id']??0);$folder=owned_folder($folderId);
      if(!$folder)throw new RuntimeException(t('folder_not_found'));
      db()->prepare('DELETE FROM research_folders WHERE id=?')->execute([$folderId]);
      flash('success',t('folder_deleted'));go('index.php?page=research');
    }
    if($action==='institutional-request'){
      $pdo=db();$institution=trim($_POST['institution_name']??'');$contact=trim($_POST['contact_name']??'');$email=strtolower(trim($_POST['contact_email']??''));
      if($institution===''||$contact===''||!filter_var($email,FILTER_VALIDATE_EMAIL))throw new RuntimeException(t('institution_required'));
      $country=($_POST['country_id']??'')!==''?(int)$_POST['country_id']:null;$plan=($_POST['plan_id']??'')!==''?(int)$_POST['plan_id']:null;
      $website=external_url($_POST['website']??'');$seats=max(1,min(5000,(int)($_POST['seats_requested']??5)));
      $s=$pdo->prepare("INSERT INTO institutional_subscriptions(plan_id,institution_name,institution_type,country_id,contact_name,contact_email,contact_phone,website,seats_requested,status,notes) VALUES(?,?,?,?,?,?,?,?,?,'inquiry',?)");
      $s->execute([$plan,$institution,trim($_POST['institution_type']??'')?:null,$country,$contact,$email,trim($_POST['contact_phone']??'')?:null,$website?:null,$seats,trim($_POST['notes']??'')?:null]);
      flash('success',t('request_received'));go('index.php?page=institutional-subscriptions');
    }
  }catch(Throwable $e){
    flash('error',$e->getMessage());
    $fallback=$_POST['return_page']??$page;
    go('index.php?page='.rawurlencode((string)$fallback));
  }
}

function public_header(string $title,string $current=''): void {
  $lang=current_language();$dir=language_direction();$user=research_user(); ?>
<!doctype html>
<html lang="<?=e($lang)?>" dir="<?=e($dir)?>">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="<?=e(t('site_description'))?>">
  <meta name="theme-color" content="#003567"><title><?=$current==='home'?'African Disputes Resolution':e($title).' | African Disputes Resolution'?></title>
  <link rel="icon" type="image/svg+xml" href="<?=url('assets/favicon.svg')?>"><link rel="icon" href="<?=url('favicon.ico')?>" sizes="any"><link rel="icon" type="image/png" href="<?=url('assets/favicon-128.png')?>"><link rel="stylesheet" href="<?=url('assets/style.css')?>"><link rel="stylesheet" href="<?=url('assets/features.css')?>"><link rel="stylesheet" href="<?=url('assets/modern.css')?>?v=20260808-countries-3">
  <script defer src="<?=url('assets/app.js')?>?v=20260808-countries-3"></script>
</head>
<body class="lang-<?=e($lang)?> page-<?=e($current?:'standard')?>"><a class="skip" href="#main"><?=e(t('skip'))?></a>
<header class="site-header modern-header">
  <div class="container nav-row modern-nav-row">
    <a class="brand modern-brand" href="<?=url('index.php')?>" aria-label="African Disputes Resolution home"><img src="<?=url('assets/logo-mark.svg')?>" alt="" width="52" height="52"><span><strong>African Disputes Resolution</strong><small><?=e(t('brand_tagline'))?></small></span></a>
    <button class="nav-toggle" data-nav-toggle aria-expanded="false"><?=icon('menu')?><span class="sr"><?=e(t('menu'))?></span></button>
    <nav class="main-nav modern-main-nav" data-nav>
      <div class="nav-primary">
        <a class="<?=$current==='home'?'active':''?>" href="<?=url('index.php')?>"><?=e(t('home'))?></a>
        <a class="<?=$current==='countries'?'active':''?>" href="<?=url('index.php?page=countries')?>"><?=e(t('countries'))?></a>
        <a class="<?=$current==='cases'?'active':''?>" href="<?=url('index.php?page=cases')?>"><?=e(t('case_law'))?></a>
        <a class="<?=$current==='regimes'?'active':''?>" href="<?=url('index.php?page=regimes')?>"><?=e(t('regional'))?></a>
        <a class="<?=$current==='institutions'?'active':''?>" href="<?=url('index.php?page=institutions')?>"><?=e(t('institutions'))?></a>
      </div>
      <div class="nav-actions">
        <a class="nav-search" href="<?=url('index.php?page=search')?>" aria-label="<?=e(t('search'))?>"><?=icon('search')?><span><?=e(t('search'))?></span></a>
        <a class="workspace-link <?=$current==='research'?'active':''?>" href="<?=url('index.php?page='.($user?'research':'research-login'))?>"><?=icon('book')?> <span><?=e($user?t('research'):t('sign_in'))?></span></a>
        <form class="language-picker" method="get" aria-label="<?=e(t('language'))?>"><?=language_switcher_fields()?><select name="lang" onchange="this.form.submit()" aria-label="<?=e(t('language'))?>"><?php foreach(supported_languages() as $code=>$language):?><option value="<?=e($code)?>" <?=$lang===$code?'selected':''?>><?=e(strtoupper($code))?></option><?php endforeach;?></select></form>
      </div>
    </nav>
  </div>
</header>
<?php foreach(flashes() as [$type,$message]):?><div class="flash <?=e($type)?>"><div class="container"><?=e($message)?></div></div><?php endforeach;?>
<main id="main">
<?php }

function public_footer(): void { ?>
</main><footer class="modern-footer"><div class="container footer-top"><div class="footer-intro"><a class="brand modern-brand footer-brand" href="<?=url('index.php')?>"><img src="<?=url('assets/logo-mark.svg')?>" alt="" width="50" height="50"><span><strong>African Disputes Resolution</strong><small><?=e(t('brand_tagline'))?></small></span></a><p><?=e(t('footer_text'))?></p></div><div class="footer-links"><div><h2><?=e(t('explore'))?></h2><a href="<?=url('index.php?page=countries')?>"><?=e(t('countries'))?></a><a href="<?=url('index.php?page=cases')?>"><?=e(t('case_law'))?></a><a href="<?=url('index.php?page=regimes')?>"><?=e(t('regional'))?></a><a href="<?=url('index.php?page=institutions')?>"><?=e(t('institutions'))?></a></div><div><h2><?=e(t('platform'))?></h2><a href="<?=url('index.php?page=about')?>"><?=e(t('about'))?></a><a href="<?=url('index.php?page=subscribe')?>"><?=e(t('subscribe'))?></a><a href="<?=url('index.php?page=institutional-subscriptions')?>"><?=e(t('institutional'))?></a><a href="<?=url('index.php?page=terms')?>"><?=e(t('terms'))?></a><a href="<?=url('index.php?page=privacy')?>"><?=e(t('privacy'))?></a></div><div><h2><?=e(t('research'))?></h2><a href="<?=url('index.php?page=research')?>"><?=e(t('route_research'))?></a><a href="<?=url('index.php?page=search')?>"><?=e(t('search'))?></a><a href="<?=url('admin.php')?>"><?=e(t('admin'))?></a><a href="mailto:info@africaadrlaw.org">info@africaadrlaw.org</a></div></div></div><div class="container footer-bottom"><span>© <?=date('Y')?> African Disputes Resolution · Kampala, Uganda</span><span><?=e(t('not_advice'))?></span></div></footer></body></html><?php }

function page_hero(string $eyebrow,string $title,string $text): void { ?><section class="page-hero"><div class="container"><div class="crumb"><a href="<?=url('index.php')?>"><?=e(t('home'))?></a><span>/</span><span><?=e($title)?></span></div><span class="eyebrow"><?=e($eyebrow)?></span><h1><?=e($title)?></h1><p><?=e($text)?></p></div></section><?php }

function entity_url(string $type,int $id): string {
  return match($type){'document'=>url('index.php?page=document&id='.$id),'case'=>url('index.php?page=case&id='.$id),'institution'=>url('index.php?page=institutions#institution-'.$id),default=>url('index.php')};
}

function save_to_folder_widget(string $type,int $id): void {
  if(!research_user()){ ?><a class="save-research-link" href="<?=url('index.php?page=research-login')?>"><?=icon('book')?> <?=e(t('save_to_folder'))?></a><?php return; }
  $folders=user_folders();if(!$folders){ ?><a class="save-research-link" href="<?=url('index.php?page=research')?>"><?=e(t('create_folder'))?></a><?php return; }
  $return=$_SERVER['REQUEST_URI']??'index.php'; ?>
  <form class="save-research-form" method="post"><?=csrf_field()?><input type="hidden" name="action" value="save-item"><input type="hidden" name="entity_type" value="<?=e($type)?>"><input type="hidden" name="entity_id" value="<?=$id?>"><input type="hidden" name="return_to" value="<?=e($return)?>"><select name="folder_id" aria-label="<?=e(t('select_folder'))?>"><?php foreach($folders as $folder):?><option value="<?=$folder['id']?>"><?=e($folder['name'])?></option><?php endforeach;?></select><button><?=e(t('save_to_folder'))?></button></form>
<?php }

if($page==='home'){
  public_header(t('page_title_home'),'home');$pdo=db_or_null();$recent=[];
  $homeStats=['countries'=>54,'documents'=>0,'cases'=>0,'institutions'=>0];
  if($pdo&&installed()&&platform_schema_ready($pdo)){
    try{
      $recent=$pdo->query("SELECT 'Document' kind,d.id,d.title,d.summary,COALESCE(d.published_at,d.updated_at) item_date,COALESCE(c.name,'Regional') country,'document' entity_type FROM documents d LEFT JOIN countries c ON c.id=d.country_id WHERE d.is_published=1 UNION ALL SELECT 'Case law',ca.id,ca.case_name,ca.summary,COALESCE(ca.decision_date,ca.updated_at),c.name,'case' FROM cases ca JOIN countries c ON c.id=ca.country_id WHERE ca.is_published=1 ORDER BY item_date DESC LIMIT 6")->fetchAll();
      $homeStats['documents']=(int)$pdo->query("SELECT COUNT(*) FROM documents WHERE is_published=1")->fetchColumn();
      $homeStats['cases']=(int)$pdo->query("SELECT COUNT(*) FROM cases WHERE is_published=1")->fetchColumn();
      $homeStats['institutions']=(int)$pdo->query("SELECT COUNT(*) FROM institutions WHERE is_published=1")->fetchColumn();
    }catch(Throwable){}
  }
  if(!$recent)$recent=[['kind'=>t('demo_case_kind'),'id'=>0,'title'=>t('demo_case_title'),'summary'=>t('demo_case_summary'),'item_date'=>'2026-07-28','country'=>'Uganda','entity_type'=>'case'],['kind'=>t('demo_protocol_kind'),'id'=>0,'title'=>t('demo_protocol_title'),'summary'=>t('demo_protocol_summary'),'item_date'=>'2026-07-18','country'=>t('regional_label'),'entity_type'=>'document'],['kind'=>t('demo_legislation_kind'),'id'=>0,'title'=>t('demo_legislation_title'),'summary'=>t('demo_legislation_summary'),'item_date'=>'2026-07-04','country'=>'Kenya','entity_type'=>'document']];
  foreach($recent as &$recentItem){$recentItem['kind']=entity_type_label($recentItem['entity_type']);if($recentItem['country']==='Regional')$recentItem['country']=t('regional_label');}unset($recentItem);
  $popularCodes=['UGA','KEN','NGA','ZAF','EGY','GHA','RWA','TZA'];$popularCountries=[];foreach(country_data() as $country){if(in_array($country[0],$popularCodes,true))$popularCountries[]=$country;}
?>
<section class="hero hero-modern">
  <div class="hero-orbit orbit-one" aria-hidden="true"></div><div class="hero-orbit orbit-two" aria-hidden="true"></div>
  <div class="container hero-modern-grid">
    <div class="hero-main-copy">
      <span class="eyebrow hero-kicker"><i></i><?=e(t('hero_eyebrow'))?></span>
      <h1><?=e(t('hero_title'))?></h1>
      <p><?=e(t('hero_text'))?></p>
      <form class="hero-search modern-hero-search" action="<?=url('index.php')?>" method="get"><input type="hidden" name="page" value="search"><span class="search-icon"><?=icon('search')?></span><input name="q" type="search" placeholder="<?=e(t('hero_placeholder'))?>"><button><?=e(t('search'))?> <span>→</span></button></form>
      <div class="hero-shortcuts"><span><?=e(t('popular'))?></span><a href="<?=url('index.php?page=cases&country=UGA')?>"><?=e(t('uganda_cases'))?></a><a href="<?=url('index.php?page=cases&primary=Arbitration+agreement')?>"><?=e(t('arbitration_agreements'))?></a><a href="<?=url('index.php?page=regimes#afcfta')?>">AfCFTA</a></div>
    </div>
    <aside class="research-navigator" aria-label="Research navigator">
      <div class="navigator-head"><div><span><?=e(t('research'))?></span><strong><?=e(t('coverage'))?></strong></div><span class="live-chip"><i></i><?=e(t('structured'))?></span></div>
      <div class="navigator-grid">
        <a href="<?=url('index.php?page=countries')?>"><i><?=icon('globe')?></i><span><strong><?=e(t('route_countries'))?></strong><small><?=e(t('route_countries_text'))?></small></span><b>→</b></a>
        <a href="<?=url('index.php?page=cases')?>"><i><?=icon('scale')?></i><span><strong><?=e(t('route_cases'))?></strong><small><?=e(t('route_cases_text'))?></small></span><b>→</b></a>
        <a href="<?=url('index.php?page=regimes')?>"><i><?=icon('book')?></i><span><strong><?=e(t('route_regimes'))?></strong><small><?=e(t('route_regimes_text'))?></small></span><b>→</b></a>
        <a href="<?=url('index.php?page=institutions')?>"><i><?=icon('building')?></i><span><strong><?=e(t('route_institutions'))?></strong><small><?=e(t('route_institutions_text'))?></small></span><b>→</b></a>
      </div>
      <div class="navigator-foot"><span><?=e(t('trust_versions'))?></span><span><?=e(t('trust_sources'))?></span></div>
    </aside>
  </div>
  <div class="container hero-metrics" aria-label="Platform coverage">
    <a href="<?=url('index.php?page=countries')?>"><span class="metric-icon"><?=icon('globe')?></span><span><strong><?=$homeStats['countries']?></strong><small><?=e(t('countries'))?></small></span></a>
    <a href="<?=url('index.php?page=search')?>"><span class="metric-icon"><?=icon('file')?></span><span><strong><?=number_format($homeStats['documents'])?></strong><small><?=e(t('documents'))?></small></span></a>
    <a href="<?=url('index.php?page=cases')?>"><span class="metric-icon"><?=icon('scale')?></span><span><strong><?=number_format($homeStats['cases'])?></strong><small><?=e(t('case_law'))?></small></span></a>
    <a href="<?=url('index.php?page=institutions')?>"><span class="metric-icon"><?=icon('building')?></span><span><strong><?=number_format($homeStats['institutions'])?></strong><small><?=e(t('institutions'))?></small></span></a>
  </div>
</section>

<section class="research-paths section-tight">
  <div class="container">
    <div class="section-heading centered-heading"><span class="eyebrow"><?=e(t('explore'))?></span><h2><?=e(t('routes_title'))?></h2><p><?=e(t('routes_text'))?></p></div>
    <div class="research-path-grid">
      <a href="<?=url('index.php?page=countries')?>"><span class="path-number">01</span><i><?=icon('globe')?></i><div><h3><?=e(t('route_countries'))?></h3><p><?=e(t('route_countries_text'))?></p></div><b>→</b></a>
      <a href="<?=url('index.php?page=cases')?>"><span class="path-number">02</span><i><?=icon('scale')?></i><div><h3><?=e(t('route_cases'))?></h3><p><?=e(t('route_cases_text'))?></p></div><b>→</b></a>
      <a href="<?=url('index.php?page=regimes')?>"><span class="path-number">03</span><i><?=icon('book')?></i><div><h3><?=e(t('route_regimes'))?></h3><p><?=e(t('route_regimes_text'))?></p></div><b>→</b></a>
      <a href="<?=url('index.php?page=institutions')?>"><span class="path-number">04</span><i><?=icon('building')?></i><div><h3><?=e(t('route_institutions'))?></h3><p><?=e(t('route_institutions_text'))?></p></div><b>→</b></a>
    </div>
  </div>
</section>

<section class="section framework-section">
  <div class="container">
    <div class="section-heading split-heading"><div><span class="eyebrow"><?=e(t('regional'))?></span><h2><?=e(t('route_regimes'))?></h2><p><?=e(t('route_regimes_text'))?></p></div><a class="text-link" href="<?=url('index.php?page=regimes')?>"><?=e(t('view_all'))?> <span>→</span></a></div>
    <div class="framework-rail"><?php foreach(regimes() as $index=>$regime):?><a href="<?=url('index.php?page=regimes#'.$regime[0])?>"><span class="framework-index"><?=str_pad((string)($index+1),2,'0',STR_PAD_LEFT)?></span><div><strong><?=e($regime[1])?></strong><h3><?=e($regime[2])?></h3><p><?=e($regime[3])?></p></div><b>↗</b></a><?php endforeach;?></div>
  </div>
</section>

<section class="section country-discovery">
  <div class="container country-discovery-grid">
    <div class="country-discovery-copy"><span class="eyebrow"><?=e(t('countries'))?></span><h2><?=e(t('country_research_title'))?></h2><p><?=e(t('country_research_intro'))?></p><div class="region-links"><?php foreach(array_values(array_unique(array_column(country_data(),2))) as $region):?><a href="<?=url('index.php?page=countries&region='.rawurlencode(country_region_slug($region)))?>"><?=e(localized_region($region))?><span>→</span></a><?php endforeach;?></div><a class="button modern-primary" href="<?=url('index.php?page=countries')?>"><?=e(t('view_all'))?> <?=e(t('countries'))?> <span>→</span></a></div>
    <div class="jurisdiction-panel"><div class="jurisdiction-panel-head"><span><?=e(t('browse'))?></span><strong><?=e(t('countries'))?></strong><a href="<?=url('index.php?page=countries')?>"><?=e(t('search'))?> →</a></div><div class="jurisdiction-list"><?php foreach($popularCountries as $country):$flag=strtolower(country_alpha2($country[0]));?><a href="<?=url('index.php?page=country&code='.$country[0])?>"><span class="country-flag-wrap"><img class="country-flag" src="https://flagcdn.com/w80/<?=e($flag)?>.png" srcset="https://flagcdn.com/w160/<?=e($flag)?>.png 2x" width="44" height="30" loading="lazy" alt="<?=e($country[1].' flag')?>"></span><span><strong><?=e($country[1])?></strong><small><?=e(localized_region($country[2]))?></small></span><b>→</b></a><?php endforeach;?></div></div>
  </div>
</section>

<section class="section latest-section"><div class="container"><div class="section-heading split-heading"><div><span class="eyebrow"><?=e(t('editorial_watch'))?></span><h2><?=e(t('recent_additions'))?></h2></div><a class="text-link" href="<?=url('index.php?page=search')?>"><?=e(t('view_all'))?> <span>→</span></a></div><div class="latest-stream"><?php foreach($recent as $item):?><article><div class="latest-meta"><span class="type-pill"><?=e($item['kind'])?></span><span><?=e($item['country'])?></span><time><?=e(date('j M Y',strtotime($item['item_date']?:'now')))?></time></div><div class="latest-main"><i><?=$item['entity_type']==='case'?icon('scale'):icon('file')?></i><div><h3><?php if($item['id']):?><a href="<?=entity_url($item['entity_type'],(int)$item['id'])?>"><?=e($item['title'])?></a><?php else:?><?=e($item['title'])?><?php endif;?></h3><p><?=e(excerpt($item['summary']))?></p></div><b>→</b></div></article><?php endforeach;?></div></div></section>

<section class="research-cta"><div class="container research-cta-inner"><div><span class="eyebrow hero-kicker"><i></i><?=e(t('research'))?></span><h2><?=e(t('route_research'))?></h2><p><?=e(t('route_research_text'))?></p></div><div class="research-cta-actions"><a class="button light-button" href="<?=url('index.php?page='.(research_user()?'research':'research-register'))?>"><?=e(t('start_research'))?> →</a><a href="<?=url('index.php?page=institutional-subscriptions')?>"><?=e(t('institutional'))?> <span>↗</span></a></div></div></section>

<section class="newsletter-modern"><div class="container"><div><span class="eyebrow"><?=e(t('editorial_watch'))?></span><h2><?=e(t('updates_title'))?></h2><p><?=e(t('updates_text'))?></p></div><form action="<?=url('index.php?page=subscribe')?>" method="post"><?=csrf_field()?><input type="hidden" name="action" value="newsletter-subscribe"><label><span class="sr"><?=e(t('email'))?></span><input type="email" name="email" placeholder="you@example.com" required></label><button><?=e(t('subscribe'))?> <span>→</span></button></form></div></section>
<?php public_footer();exit;}

if($page==='regimes'){
  $pdo=db_or_null();$documents=[];if($pdo&&installed()&&platform_schema_ready($pdo)){$documents=$pdo->query("SELECT d.*,c.name country_name FROM documents d LEFT JOIN countries c ON c.id=d.country_id WHERE d.is_published=1 AND d.regime_key IS NOT NULL ORDER BY d.regime_key,d.effective_date DESC,d.year DESC,d.title")->fetchAll();}
  public_header(t('regional'),'regimes');page_hero(t('regional'),t('route_regimes'),t('route_regimes_text')); ?>
<section class="section"><div class="container regime-page-grid"><?php foreach(regimes() as $regime):$items=array_values(array_filter($documents,fn($d)=>$d['regime_key']===$regime[0]));?><article class="regime-card" id="<?=e($regime[0])?>"><header><span><?=e($regime[1])?></span><i><?=icon('globe')?></i></header><h2><?=e($regime[2])?></h2><p><?=e($regime[3])?></p><?php if($items):?><div class="doc-list compact-list"><?php foreach($items as $doc):?><a href="<?=url('index.php?page=document&id='.$doc['id'])?>"><i><?=icon('file')?></i><span><strong><?=e($doc['title'])?></strong><small><?=e(legal_status_label($doc['legal_status'])).' · '.e($doc['effective_date']?:$doc['year']?:'—')?></small></span><b>→</b></a><?php endforeach;?></div><?php else:?><small class="empty-note"><?=e(t('no_published_materials'))?></small><?php endif;?></article><?php endforeach;?></div></section>
<?php public_footer();exit;}

if($page==='countries'){
  $allCountries=country_data();
  $regions=array_values(array_unique(array_column($allCountries,2)));
  $requestedRegion=strtolower(trim((string)($_GET['region']??'')));
  $activeRegionName=country_region_from_slug($requestedRegion);
  $activeRegionSlug=$activeRegionName?country_region_slug($activeRegionName):'all';
  $visibleCountries=$activeRegionName
    ? array_values(array_filter($allCountries,fn($country)=>$country[2]===$activeRegionName))
    : $allCountries;
  public_header(t('countries'),'countries');page_hero(t('route_countries'),t('country_count'),t('country_coverage')); ?>
<section class="section countries-directory-section"><div class="container"><div class="country-tools"><label class="country-search"><?=icon('search')?><input data-country-filter type="search" placeholder="<?=e(t('search'))?> <?=e(t('countries'))?>…"></label><nav class="region-tabs" aria-label="Filter countries by region"><a class="<?=$activeRegionSlug==='all'?'active':''?>" aria-current="<?=$activeRegionSlug==='all'?'page':'false'?>" href="<?=url('index.php?page=countries')?>"><?=e(t('all_countries'))?></a><?php foreach($regions as $region):$regionSlug=country_region_slug($region);?><a class="<?=$activeRegionSlug===$regionSlug?'active':''?>" aria-current="<?=$activeRegionSlug===$regionSlug?'page':'false'?>" href="<?=url('index.php?page=countries&region='.rawurlencode($regionSlug))?>"><?=e(localized_region($region))?></a><?php endforeach;?></nav></div><div class="country-grid modern-country-grid" data-region-directory="<?=e($activeRegionSlug)?>"><?php foreach($visibleCountries as $country):$flag=strtolower(country_alpha2($country[0]));?><a data-country="<?=e(strtolower(trim($country[0].' '.$country[1])))?>" href="<?=url('index.php?page=country&code='.$country[0])?>"><span class="directory-flag-wrap"><img class="directory-flag" src="https://flagcdn.com/w80/<?=e($flag)?>.png" srcset="https://flagcdn.com/w160/<?=e($flag)?>.png 2x" width="48" height="32" loading="lazy" alt="<?=e($country[1].' flag')?>"></span><span class="directory-country-copy"><strong><?=e($country[1])?></strong><small><?=e(localized_region($country[2]))?></small></span><b>→</b></a><?php endforeach;?></div><div class="empty large" data-country-empty hidden><strong><?=e(t('no_results'))?></strong></div></div></section>
<?php public_footer();exit;}

if($page==='country'){
  $code=strtoupper(trim($_GET['code']??''));$fallback=country_by_code($code);if(!$fallback){http_response_code(404);public_header(t('country_not_found'));page_hero('404',t('country_not_found'),'');public_footer();exit;}
  $pdo=db_or_null();$country=$fallback;$docs=$cases=$institutions=[];
  if($pdo&&installed()&&platform_schema_ready($pdo)){$s=$pdo->prepare('SELECT * FROM countries WHERE code=?');$s->execute([$code]);$country=array_merge($fallback,$s->fetch()?:[]);$s=$pdo->prepare('SELECT * FROM documents WHERE country_id=? AND is_published=1 ORDER BY effective_date DESC,year DESC,title');$s->execute([$country['id']]);$docs=$s->fetchAll();$s=$pdo->prepare('SELECT * FROM cases WHERE country_id=? AND is_published=1 ORDER BY year DESC,decision_date DESC LIMIT 12');$s->execute([$country['id']]);$cases=$s->fetchAll();$s=$pdo->prepare('SELECT * FROM institutions WHERE country_id=? AND is_published=1 ORDER BY name');$s->execute([$country['id']]);$institutions=$s->fetchAll();}
  public_header($country['name'],'countries'); ?>
<section class="country-hero"><div class="container"><div class="crumb light"><a href="<?=url('index.php')?>"><?=e(t('home'))?></a><span>/</span><a href="<?=url('index.php?page=countries')?>"><?=e(t('countries'))?></a><span>/</span><span><?=e($country['name'])?></span></div><div class="country-title"><div><span class="country-code"><?=e($code)?></span><h1><?=e($country['name'])?></h1><p><?=e(localized_region($country['region']))?><?php if(!empty($country['common_law'])||!empty($country['common_law_index'])):?> · <?=e(t('common_law_index'))?><?php endif;?></p></div><div class="verification-stamp"><small><?=e(t('last_verified'))?></small><strong><?=e(!empty($country['last_verified_at'])?date('j M Y',strtotime($country['last_verified_at'])):t('verified_unknown'))?></strong></div></div></div></section>
<section class="section"><div class="container country-layout"><article class="country-main"><section class="content-block"><span class="eyebrow"><?=e(t('about'))?></span><h2><?=e($country['name'].' '.t('adr_overview'))?></h2><p><?=e($country['profile_summary']??t('country_materials_default'))?></p></section><section class="content-block"><div class="heading-action"><div><span class="eyebrow"><?=e(t('route_countries'))?></span><h2><?=e((string)count($docs).' '.t('published_documents'))?></h2></div></div><?php if($docs):?><div class="doc-list"><?php foreach($docs as $doc):?><a href="<?=url('index.php?page=document&id='.$doc['id'])?>"><i><?=icon('file')?></i><span><strong><?=e($doc['title'])?></strong><small><?=e(document_types()[$doc['document_type']]??$doc['document_type'])?> · <?=e(legal_status_label($doc['legal_status']))?> · <?=e($doc['effective_date']?:$doc['year']?:'—')?></small></span><span class="status-chip <?=e($doc['legal_status'])?>"><?=e(legal_status_label($doc['legal_status']))?></span></a><?php endforeach;?></div><?php else:?><div class="empty"><strong><?=e(t('no_published_documents'))?></strong></div><?php endif;?></section><section class="content-block"><span class="eyebrow"><?=e(t('case_law'))?></span><h2><?=e($country['name'].' '.t('country_case_law'))?></h2><?php if($cases):?><div class="case-list"><?php foreach($cases as $case):?><a href="<?=url('index.php?page=case&id='.$case['id'])?>"><i><?=e($case['year'])?></i><span><strong><?=e($case['case_name'])?></strong><small><?=e($case['primary_subject'])?> · <?=e($case['court'])?></small></span><b>→</b></a><?php endforeach;?></div><?php else:?><div class="empty"><strong><?=e(t('no_published_cases'))?></strong></div><?php endif;?></section></article><aside class="country-side"><section><h2><?=e(t('international_adr_status'))?></h2><dl><div><dt>ICSID</dt><dd><?=e($country['icsid_status']??t('not_recorded'))?></dd></div><div><dt>ICC</dt><dd><?=e($country['icc_status']??t('not_recorded'))?></dd></div><div><dt>New York Convention</dt><dd><?=e($country['new_york_status']??t('not_recorded'))?></dd></div><div><dt>UNCITRAL</dt><dd><?=e($country['uncitral_status']??t('not_recorded'))?></dd></div></dl></section><section><h2><?=e(t('institutions'))?></h2><?php if($institutions):?><?php foreach($institutions as $institution):?><a href="<?=url('index.php?page=institutions&country='.$code.'#institution-'.$institution['id'])?>"><strong><?=e($institution['name'])?></strong><small><?=e($institution['email']?:$institution['website'])?></small></a><?php endforeach;?><?php else:?><p><?=e(t('no_published_institutions'))?></p><?php endif;?></section><?php if(!empty($country['source_url'])):?><a class="button dark-btn" href="<?=e($country['source_url'])?>" target="_blank" rel="noopener"><?=e(t('original_source'))?> ↗</a><?php endif;?></aside></div></section>
<?php public_footer();exit;}

if($page==='cases'){
  $country=strtoupper(trim($_GET['country']??''));$primary=trim($_GET['primary']??'');$secondary=trim($_GET['secondary']??'');$year=trim($_GET['year']??'');$q=trim($_GET['q']??'');$pdo=db_or_null();$rows=[];
  if($pdo&&installed()&&platform_schema_ready($pdo)){$where=['ca.is_published=1'];$params=[];if($country){$where[]='c.code=?';$params[]=$country;}if($primary){$where[]='ca.primary_subject=?';$params[]=$primary;}if($secondary){$where[]='ca.secondary_subject=?';$params[]=$secondary;}if($year){$where[]='ca.year=?';$params[]=(int)$year;}if($q){$like='%'.$q.'%';$where[]='(ca.case_name LIKE ? OR ca.citation LIKE ? OR ca.summary LIKE ? OR ca.keywords LIKE ?)';array_push($params,$like,$like,$like,$like);}$s=$pdo->prepare('SELECT ca.*,c.name country_name,c.code country_code FROM cases ca JOIN countries c ON c.id=ca.country_id WHERE '.implode(' AND ',$where).' ORDER BY ca.year DESC,ca.decision_date DESC,ca.case_name LIMIT 300');$s->execute($params);$rows=$s->fetchAll();}
  public_header(t('case_law'),'cases');page_hero(t('case_index'),t('route_cases'),t('route_cases_text')); ?>
<section class="section"><div class="container"><form class="filters" method="get"><input type="hidden" name="page" value="cases"><label><span><?=e(t('search'))?></span><input name="q" value="<?=e($q)?>" placeholder="<?=e(t('case_search_placeholder'))?>"></label><label><span><?=e(t('country'))?></span><select name="country"><option value=""><?=e(t('all_countries'))?></option><?php foreach(country_data() as $item):?><option value="<?=e($item[0])?>" <?=$country===$item[0]?'selected':''?>><?=e($item[1])?></option><?php endforeach;?></select></label><label><span><?=e(t('primary_subject'))?></span><select name="primary"><option value=""><?=e(t('all_subjects'))?></option><?php foreach(primary_subjects() as $item):?><option <?=$primary===$item?'selected':''?>><?=e($item)?></option><?php endforeach;?></select></label><label><span><?=e(t('secondary_subject'))?></span><select name="secondary"><option value=""><?=e(t('all_subjects'))?></option><?php foreach(secondary_subjects() as $item):?><option <?=$secondary===$item?'selected':''?>><?=e($item)?></option><?php endforeach;?></select></label><label><span><?=e(t('year'))?></span><select name="year"><option value=""><?=e(t('all_years'))?></option><?php for($y=(int)date('Y');$y>=1960;$y--):?><option <?=$year==(string)$y?'selected':''?>><?=$y?></option><?php endfor;?></select></label><button><?=e(t('filter'))?></button></form><div class="results-title"><div><span class="eyebrow"><?=e(t('results'))?></span><h2><?=e(t('case_results',['count'=>count($rows)]))?></h2></div></div><?php if($rows):?><div class="case-results"><?php foreach($rows as $case):?><article><div class="case-year"><?=e($case['year'])?></div><div><span class="pill"><?=e($case['country_name'])?></span><h2><a href="<?=url('index.php?page=case&id='.$case['id'])?>"><?=e($case['case_name'])?></a></h2><p><?=e(excerpt($case['summary']))?></p><small><?=e($case['court'])?> · <?=e($case['primary_subject'])?><?php if($case['secondary_subject']):?> · <?=e($case['secondary_subject'])?><?php endif;?></small></div><a href="<?=url('index.php?page=case&id='.$case['id'])?>">→</a></article><?php endforeach;?></div><?php else:?><div class="empty large"><strong><?=e(t('no_results'))?></strong></div><?php endif;?></div></section>
<?php public_footer();exit;}

if($page==='case'){
  $id=(int)($_GET['id']??0);$pdo=db_or_null();$case=null;if($pdo&&installed()&&platform_schema_ready($pdo)){$s=$pdo->prepare('SELECT ca.*,c.name country_name,c.code country_code FROM cases ca JOIN countries c ON c.id=ca.country_id WHERE ca.id=? AND ca.is_published=1');$s->execute([$id]);$case=$s->fetch();}
  if(!$case){http_response_code(404);public_header(t('case_not_found'));page_hero('404',t('case_not_found'),'');public_footer();exit;}
  public_header($case['case_name'],'cases'); ?>
<section class="page-hero case-hero"><div class="container"><div class="crumb light"><a href="<?=url('index.php')?>"><?=e(t('home'))?></a><span>/</span><a href="<?=url('index.php?page=cases')?>"><?=e(t('case_law'))?></a><span>/</span><span><?=e($case['year'])?></span></div><span class="eyebrow gold"><?=e($case['country_name'])?> · <?=e($case['year'])?></span><h1><?=e($case['case_name'])?></h1><p><?=e($case['citation'])?></p><div class="hero-actions"><?php if($case['file_path']):?><a class="button" href="<?=url('download.php?case='.$case['id'])?>"><?=e(t('download'))?></a><?php endif;?><?php if($case['source_url']):?><a class="button ghost" href="<?=e($case['source_url'])?>" target="_blank" rel="noopener"><?=e(t('original_source'))?> ↗</a><?php endif;?><?php save_to_folder_widget('case',(int)$case['id']);?></div></div></section>
<section class="section"><div class="container case-detail"><article class="prose"><?php if($case['summary']):?><section><h2><?=e(t('summary'))?></h2><p><?=nl2br(e($case['summary']))?></p></section><?php endif;?><?php if($case['key_holding']):?><section><h2><?=e(t('key_holding'))?></h2><p><?=nl2br(e($case['key_holding']))?></p></section><?php endif;?></article><aside class="case-facts"><h2><?=e(t('document_details'))?></h2><dl><div><dt><?=e(t('country'))?></dt><dd><?=e($case['country_name'])?></dd></div><div><dt><?=e(t('court'))?></dt><dd><?=e($case['court'])?></dd></div><div><dt><?=e(t('decision_date'))?></dt><dd><?=e($case['decision_date']?:$case['year'])?></dd></div><div><dt><?=e(t('primary_subject'))?></dt><dd><?=e($case['primary_subject'])?></dd></div><div><dt><?=e(t('secondary_subject'))?></dt><dd><?=e($case['secondary_subject']?:'—')?></dd></div><div><dt><?=e(t('content_language'))?></dt><dd><?=e(language_name($case['language_code']??'en'))?></dd></div><div><dt><?=e(t('last_verified'))?></dt><dd><?=e($case['last_verified_at']?:t('verified_unknown'))?></dd></div></dl></aside></div></section>
<?php public_footer();exit;}

if($page==='document'){
  $id=(int)($_GET['id']??0);$pdo=db_or_null();$doc=null;$versions=[];
  if($pdo&&installed()&&platform_schema_ready($pdo)){$s=$pdo->prepare('SELECT d.*,c.name country_name,c.code country_code,s.title supersedes_title,r.title repealed_by_title FROM documents d LEFT JOIN countries c ON c.id=d.country_id LEFT JOIN documents s ON s.id=d.supersedes_document_id LEFT JOIN documents r ON r.id=d.repealed_by_document_id WHERE d.id=? AND d.is_published=1');$s->execute([$id]);$doc=$s->fetch();if($doc)$versions=document_versions($id,true);}
  if(!$doc){http_response_code(404);public_header(t('document_not_found'));page_hero('404',t('document_not_found'),'');public_footer();exit;}
  $scope=$doc['country_name']?:($doc['regime_key']?strtoupper((string)$doc['regime_key']):t('regional_label'));
  public_header($doc['title'],$doc['country_name']?'countries':'regimes'); ?>
<section class="page-hero document-hero"><div class="container"><div class="crumb"><a href="<?=url('index.php')?>"><?=e(t('home'))?></a><span>/</span><span><?=e($scope)?></span><span>/</span><span><?=e($doc['title'])?></span></div><div class="document-status-row"><span class="status-chip <?=e($doc['legal_status'])?>"><?=e(legal_status_label($doc['legal_status']))?></span><span><?=e(language_name($doc['language_code']))?></span><?php if($doc['version_label']):?><span><?=e($doc['version_label'])?></span><?php endif;?></div><h1><?=e($doc['title'])?></h1><p><?=e($doc['summary'])?></p><div class="hero-actions"><a class="button dark-btn" href="<?=url('download.php?id='.$doc['id'])?>"><?=e(t('download'))?></a><?php if($doc['source_url']):?><a class="button outline" href="<?=e($doc['source_url'])?>" target="_blank" rel="noopener"><?=e(t('original_source'))?> ↗</a><?php endif;?><?php save_to_folder_widget('document',(int)$doc['id']);?></div></div></section>
<?php if($doc['legal_status']==='repealed'||$doc['legal_status']==='superseded'):?><div class="legal-alert <?=e($doc['legal_status'])?>"><div class="container"><strong><?=e($doc['legal_status']==='repealed'?t('repealed_notice'):t('superseded_notice'))?></strong><?php if($doc['repealed_by_title']):?><span><?=e($doc['repealed_by_title'])?></span><?php endif;?></div></div><?php endif;?>
<section class="section"><div class="container document-layout"><article class="prose"><section><h2><?=e(t('summary'))?></h2><p><?=nl2br(e($doc['summary']?:t('no_editorial_summary')))?></p></section><?php if($doc['verification_notes']):?><section><h2><?=e(t('verification'))?></h2><p><?=nl2br(e($doc['verification_notes']))?></p><?php if($doc['verification_source']):?><a href="<?=e($doc['verification_source'])?>" target="_blank" rel="noopener"><?=e(t('original_source'))?> ↗</a><?php endif;?></section><?php endif;?><section><div class="heading-action"><div><span class="eyebrow"><?=e(t('version_history'))?></span><h2><?=e(t('archived_revisions',['count'=>count($versions)]))?></h2></div></div><?php if($versions):?><div class="version-timeline"><?php foreach($versions as $version):?><article><span><?=e(t('revision'))?> <?=e($version['revision_no'])?></span><div><strong><?=e($version['version_label']?:$version['title'])?></strong><small><?=e(date('j M Y, H:i',strtotime($version['created_at'])))?> · <?=e($version['changed_by_name']?:t('administrator'))?> · <?=e(legal_status_label($version['legal_status']))?></small><?php if($version['change_note']):?><p><?=e($version['change_note'])?></p><?php endif;?></div><?php if($version['file_path']):?><a href="<?=url('version-download.php?id='.$version['id'])?>"><?=e(t('download'))?></a><?php endif;?></article><?php endforeach;?></div><?php else:?><div class="empty"><strong><?=e(t('current_version'))?></strong><p><?=e(t('no_earlier_revisions'))?></p></div><?php endif;?></section></article><aside class="case-facts document-facts"><h2><?=e(t('document_details'))?></h2><dl><div><dt><?=e(t('country'))?></dt><dd><?=e($scope)?></dd></div><div><dt><?=e(t('document_type'))?></dt><dd><?=e(document_types()[$doc['document_type']]??$doc['document_type'])?></dd></div><div><dt><?=e(t('document_number'))?></dt><dd><?=e($doc['document_number']?:'—')?></dd></div><div><dt><?=e(t('legal_status'))?></dt><dd><?=e(legal_status_label($doc['legal_status']))?></dd></div><div><dt><?=e(t('effective_date'))?></dt><dd><?=e($doc['effective_date']?:'—')?></dd></div><div><dt><?=e(t('repeal_date'))?></dt><dd><?=e($doc['repeal_date']?:'—')?></dd></div><div><dt><?=e(t('last_verified'))?></dt><dd><?=e($doc['last_verified_at']?:t('verified_unknown'))?></dd></div><div><dt><?=e(t('content_language'))?></dt><dd><?=e(language_name($doc['language_code']))?></dd></div></dl><?php if($doc['supersedes_title']):?><div class="relationship-note"><small><?=e(t('supersedes'))?></small><strong><?=e($doc['supersedes_title'])?></strong></div><?php endif;?></aside></div></section>
<?php public_footer();exit;}

if($page==='institutions'){
  $pdo=db_or_null();$items=[];$country=strtoupper(trim($_GET['country']??''));$q=trim($_GET['q']??'');if($pdo&&installed()&&platform_schema_ready($pdo)){$where=['i.is_published=1'];$params=[];if($country){$where[]='c.code=?';$params[]=$country;}if($q){$like='%'.$q.'%';$where[]='(i.name LIKE ? OR i.description LIKE ?)';array_push($params,$like,$like);}$s=$pdo->prepare('SELECT i.*,c.name country_name,c.code country_code FROM institutions i JOIN countries c ON c.id=i.country_id WHERE '.implode(' AND ',$where).' ORDER BY c.name,i.name');$s->execute($params);$items=$s->fetchAll();}
  public_header(t('institutions'),'institutions');page_hero(t('institutions'),t('route_institutions'),t('route_institutions_text')); ?>
<section class="section"><div class="container"><form class="directory-search"><input type="hidden" name="page" value="institutions"><label><?=icon('search')?><input name="q" value="<?=e($q)?>" placeholder="<?=e(t('search'))?>…"></label><select name="country"><option value=""><?=e(t('all_countries'))?></option><?php foreach(country_data() as $entry):?><option value="<?=e($entry[0])?>" <?=$country===$entry[0]?'selected':''?>><?=e($entry[1])?></option><?php endforeach;?></select><button><?=e(t('search'))?></button></form><div class="results-title"><div><span class="eyebrow"><?=e(t('results'))?></span><h2><?=e((string)count($items).' '.t('institutions'))?></h2></div></div><?php if($items):?><div class="institution-grid"><?php foreach($items as $institution):?><article id="institution-<?=$institution['id']?>"><header><i><?=icon('building')?></i><span class="pill"><?=e($institution['country_name'])?></span></header><h3><?=e($institution['name'])?></h3><p><?=e(excerpt($institution['description']?:$institution['address']))?></p><small><?=e($institution['email'])?><br><?=e($institution['phone'])?><br><?=e(t('last_verified'))?>: <?=e($institution['last_verified_at']?:t('verified_unknown'))?></small><div class="institution-actions"><?php if($institution['website']):?><a href="<?=e($institution['website'])?>" target="_blank" rel="noopener"><?=e(t('website'))?> →</a><?php endif;?><?php save_to_folder_widget('institution',(int)$institution['id']);?></div></article><?php endforeach;?></div><?php else:?><div class="empty large"><strong><?=e(t('no_results'))?></strong></div><?php endif;?></div></section>
<?php public_footer();exit;}

if($page==='search'){
  $q=trim($_GET['q']??'');$pdo=db_or_null();$docs=$cases=$institutions=[];if($q&&$pdo&&installed()&&platform_schema_ready($pdo)){$like='%'.$q.'%';$s=$pdo->prepare('SELECT d.*,c.name country_name FROM documents d LEFT JOIN countries c ON c.id=d.country_id WHERE d.is_published=1 AND (d.title LIKE ? OR d.summary LIKE ? OR d.keywords LIKE ? OR d.document_number LIKE ?) ORDER BY d.published_at DESC LIMIT 50');$s->execute([$like,$like,$like,$like]);$docs=$s->fetchAll();$s=$pdo->prepare('SELECT ca.*,c.name country_name FROM cases ca JOIN countries c ON c.id=ca.country_id WHERE ca.is_published=1 AND (ca.case_name LIKE ? OR ca.summary LIKE ? OR ca.citation LIKE ? OR ca.keywords LIKE ?) ORDER BY ca.year DESC LIMIT 50');$s->execute([$like,$like,$like,$like]);$cases=$s->fetchAll();$s=$pdo->prepare('SELECT i.*,c.name country_name FROM institutions i JOIN countries c ON c.id=i.country_id WHERE i.is_published=1 AND (i.name LIKE ? OR i.description LIKE ?) ORDER BY i.name LIMIT 50');$s->execute([$like,$like]);$institutions=$s->fetchAll();}$total=count($docs)+count($cases)+count($institutions);
  public_header($q?t('search').': '.$q:t('search')); ?>
<section class="page-hero"><div class="container"><span class="eyebrow"><?=e(t('search'))?></span><h1><?=$q?'“'.e($q).'”':e(t('search'))?></h1><form class="hero-search light-search"><input type="hidden" name="page" value="search"><?=icon('search')?><input name="q" value="<?=e($q)?>" type="search" placeholder="<?=e(t('hero_placeholder'))?>"><button><?=e(t('search'))?></button></form></div></section><section class="section"><div class="container"><?php if(!$q):?><div class="empty large"><strong><?=e(t('hero_placeholder'))?></strong></div><?php elseif(!$total):?><div class="empty large"><strong><?=e(t('no_results'))?></strong></div><?php else:?><div class="results-title"><div><span class="eyebrow"><?=e(t('results'))?></span><h2><?=e(t('results_count',['count'=>$total]))?></h2></div></div><?php if($docs):?><section class="search-group"><h2><?=e(t('documents'))?> <span><?=count($docs)?></span></h2><div class="doc-list"><?php foreach($docs as $doc):?><a href="<?=url('index.php?page=document&id='.$doc['id'])?>"><i><?=icon('file')?></i><span><strong><?=e($doc['title'])?></strong><small><?=e($doc['country_name']?:t('regional_label'))?> · <?=e(legal_status_label($doc['legal_status']))?></small></span><b>→</b></a><?php endforeach;?></div></section><?php endif;?><?php if($cases):?><section class="search-group"><h2><?=e(t('case_law'))?> <span><?=count($cases)?></span></h2><div class="case-list"><?php foreach($cases as $case):?><a href="<?=url('index.php?page=case&id='.$case['id'])?>"><i><?=e($case['year'])?></i><span><strong><?=e($case['case_name'])?></strong><small><?=e($case['country_name'].' · '.$case['primary_subject'])?></small></span><b>→</b></a><?php endforeach;?></div></section><?php endif;?><?php if($institutions):?><section class="search-group"><h2><?=e(t('institutions'))?> <span><?=count($institutions)?></span></h2><div class="institution-list"><?php foreach($institutions as $institution):?><article><i><?=icon('building')?></i><div><h3><?=e($institution['name'])?></h3><p><?=e($institution['country_name'])?></p></div></article><?php endforeach;?></div></section><?php endif;?><?php endif;?></div></section>
<?php public_footer();exit;}

if($page==='research-login'||$page==='research-register'){
  if(research_user())go('index.php?page=research');$register=$page==='research-register';public_header($register?t('register'):t('login'),'research');page_hero(t('research'),$register?t('register'):t('login'),t('research_text')); ?>
<section class="section"><div class="narrow"><form class="form-card research-auth" method="post"><?=csrf_field()?><input type="hidden" name="action" value="<?=$register?'research-register':'research-login'?>"><input type="hidden" name="return_page" value="<?=$page?>"><h2><?=e($register?t('register'):t('login'))?></h2><?php if($register):?><label><span><?=e(t('name'))?></span><input name="name" required></label><label><span><?=e(t('organization'))?> <small>(<?=e(t('optional'))?>)</small></span><input name="organization"></label><?php endif;?><label><span><?=e(t('email'))?></span><input type="email" name="email" required></label><label><span><?=e(t('password'))?></span><input type="password" name="password" minlength="<?=$register?10:1?>" required></label><?php if($register):?><label><span><?=e(t('confirm_password'))?></span><input type="password" name="confirm_password" minlength="10" required></label><?php endif;?><button><?=e($register?t('register'):t('sign_in'))?></button><p class="auth-switch"><?php if($register):?><?=e(t('already_registered'))?> <a href="<?=url('index.php?page=research-login')?>"><?=e(t('sign_in'))?></a><?php else:?><?=e(t('need_account'))?> <a href="<?=url('index.php?page=research-register')?>"><?=e(t('register'))?></a><?php endif;?></p></form></div></section>
<?php public_footer();exit;}

if($page==='research'){
  require_research();$folders=user_folders();$memberships=institutional_memberships();public_header(t('research_title'),'research');page_hero(t('research'),t('research_title'),t('research_text')); ?>
<section class="section"><div class="container research-dashboard"><aside class="research-profile"><div class="profile-avatar"><?=e(strtoupper(substr(research_user()['name'],0,1)))?></div><h2><?=e(research_user()['name'])?></h2><p><?=e(research_user()['email'])?></p><?php if(research_user()['organization']):?><small><?=e(research_user()['organization'])?></small><?php endif;?><?php if($memberships):?><div class="membership-list"><?php foreach($memberships as $membership):?><span><strong><?=e($membership['institution_name'])?></strong><small><?=e($membership['plan_name']?:t('institutional_access_generic'))?> · <?=e(membership_role_label($membership['member_role']))?></small></span><?php endforeach;?></div><?php endif;?><a href="<?=url('index.php?page=research-logout')?>"><?=e(t('sign_out'))?></a></aside><div><form class="folder-create" method="post"><?=csrf_field()?><input type="hidden" name="action" value="create-folder"><input type="hidden" name="return_page" value="research"><div><span class="eyebrow"><?=e(t('create_folder'))?></span><h2><?=e(t('route_research'))?></h2></div><label><span><?=e(t('folder_name'))?></span><input name="name" required></label><label><span><?=e(t('description'))?> <small>(<?=e(t('optional'))?>)</small></span><input name="description"></label><button><?=e(t('create_folder'))?></button></form><div class="folder-grid"><?php foreach($folders as $folder):?><a href="<?=url('index.php?page=folder&id='.$folder['id'])?>"><i><?=icon('book')?></i><span><strong><?=e($folder['name'])?></strong><small><?=e($folder['description'])?></small></span><b><?=number_format($folder['item_count'])?> <?=e(t('items'))?></b></a><?php endforeach;?></div><?php if(!$folders):?><div class="empty large"><strong><?=e(t('create_folder'))?></strong></div><?php endif;?></div></div></section>
<?php public_footer();exit;}

if($page==='folder'){
  require_research();$folderId=(int)($_GET['id']??0);$folder=owned_folder($folderId);if(!$folder){http_response_code(404);public_header(t('folder_not_found'),'research');page_hero('404',t('folder_not_found'),'');public_footer();exit;}$items=folder_items($folderId);
  public_header($folder['name'],'research');page_hero(t('research'),$folder['name'],$folder['description']?:t('research_text')); ?>
<section class="section"><div class="container"><div class="folder-toolbar"><a href="<?=url('index.php?page=research')?>">← <?=e(t('research'))?></a><div><span><?=e(t('export_citations'))?>:</span><a href="<?=url('export.php?folder='.$folderId.'&format=csv')?>"><?=e(t('export_csv'))?></a><a href="<?=url('export.php?folder='.$folderId.'&format=ris')?>"><?=e(t('export_ris'))?></a><a href="<?=url('export.php?folder='.$folderId.'&format=bibtex')?>"><?=e(t('export_bibtex'))?></a></div></div><?php if($items):?><div class="research-items"><?php foreach($items as $item):?><article><span class="entity-type"><?=e(entity_type_label($item['entity_type']))?></span><div><h2><a href="<?=entity_url($item['entity_type'],(int)$item['entity_id'])?>"><?=e($item['item_title'])?></a></h2><p><?=e(citation_for_item($item))?></p><?php if($item['note']):?><blockquote><?=e($item['note'])?></blockquote><?php endif;?></div><form method="post" onsubmit="return confirm('<?=e(t('remove_confirm'))?>')"><?=csrf_field()?><input type="hidden" name="action" value="remove-item"><input type="hidden" name="folder_id" value="<?=$folderId?>"><input type="hidden" name="item_id" value="<?=$item['id']?>"><input type="hidden" name="return_page" value="folder"><button><?=e(t('remove'))?></button></form></article><?php endforeach;?></div><?php else:?><div class="empty large"><strong><?=e(t('no_saved_materials'))?></strong><p><?=e(t('save_materials_help'))?></p></div><?php endif;?><form class="delete-folder-form" method="post" onsubmit="return confirm('<?=e(t('delete_folder_confirm'))?>')"><?=csrf_field()?><input type="hidden" name="action" value="delete-folder"><input type="hidden" name="folder_id" value="<?=$folderId?>"><input type="hidden" name="return_page" value="folder"><button><?=e(t('delete_folder'))?></button></form></div></section>
<?php public_footer();exit;}

if($page==='institutional-subscriptions'){
  $pdo=db_or_null();$plans=$countries=[];if($pdo&&installed()&&platform_schema_ready($pdo)){try{$plans=$pdo->query('SELECT * FROM subscription_plans WHERE is_active=1 ORDER BY sort_order,name')->fetchAll();$countries=$pdo->query('SELECT id,name FROM countries ORDER BY name')->fetchAll();}catch(Throwable){}}
  public_header(t('institutional_title'));page_hero(t('institutional'),t('institutional_title'),t('institutional_text')); ?>
<section class="section muted"><div class="container"><div class="split-title"><div><span class="eyebrow"><?=e(t('plans'))?></span><h2><?=e(t('research_access_teams'))?></h2></div><p><?=e(t('plans_configurable'))?></p></div><div class="plan-grid"><?php foreach($plans as $plan):?><article><span class="plan-code"><?=e($plan['name'])?></span><h2><?=$plan['annual_price']!==null?e($plan['currency'].' '.number_format((float)$plan['annual_price'])):e(t('custom_price'))?></h2><?php if($plan['annual_price']!==null):?><small><?=e(t('annual'))?></small><?php endif;?><p><?=e($plan['description'])?></p><strong><?=number_format($plan['included_seats'])?> <?=e(t('included_seats'))?></strong><ul><?php foreach(preg_split('/\r?\n/',(string)$plan['features']) as $feature):if(trim($feature)!==''):?><li><?=e($feature)?></li><?php endif;endforeach;?></ul><a href="#institution-request" class="button dark-btn"><?=e(t('request_access'))?></a></article><?php endforeach;?></div></div></section>
<section class="section" id="institution-request"><div class="narrow"><form class="form-card institution-request" method="post"><?=csrf_field()?><input type="hidden" name="action" value="institutional-request"><input type="hidden" name="return_page" value="institutional-subscriptions"><h2><?=e(t('request_access'))?></h2><label><span><?=e(t('institution_name'))?></span><input name="institution_name" required></label><div class="two-fields"><label><span><?=e(t('institution_type'))?></span><select name="institution_type"><option value=""><?=e(t('select'))?></option><option value="Law firm / chambers"><?=e(t('type_law_firm'))?></option><option value="University / library"><?=e(t('type_university'))?></option><option value="Court / tribunal"><?=e(t('type_court'))?></option><option value="Government / public body"><?=e(t('type_government'))?></option><option value="Development institution"><?=e(t('type_development'))?></option><option value="Corporate legal department"><?=e(t('type_corporate'))?></option><option value="Other"><?=e(t('type_other'))?></option></select></label><label><span><?=e(t('country'))?></span><select name="country_id"><option value=""><?=e(t('all_countries'))?></option><?php foreach($countries as $country):?><option value="<?=$country['id']?>"><?=e($country['name'])?></option><?php endforeach;?></select></label></div><div class="two-fields"><label><span><?=e(t('contact_name'))?></span><input name="contact_name" required></label><label><span><?=e(t('email'))?></span><input type="email" name="contact_email" required></label></div><div class="two-fields"><label><span><?=e(t('phone'))?></span><input name="contact_phone"></label><label><span><?=e(t('website'))?></span><input type="url" name="website"></label></div><div class="two-fields"><label><span><?=e(t('plan'))?></span><select name="plan_id"><option value=""><?=e(t('select'))?></option><?php foreach($plans as $plan):?><option value="<?=$plan['id']?>"><?=e($plan['name'])?></option><?php endforeach;?></select></label><label><span><?=e(t('seats'))?></span><input type="number" name="seats_requested" min="1" max="5000" value="10"></label></div><label><span><?=e(t('notes'))?> <small>(<?=e(t('optional'))?>)</small></span><textarea name="notes" rows="4"></textarea></label><button><?=e(t('submit_request'))?></button></form></div></section>
<?php public_footer();exit;}

if($page==='about'){
  public_header(t('about'));page_hero(t('about'),t('about_title'),t('about_intro')); ?>
<section class="section"><div class="container prose-layout"><article class="prose"><h2><?=e(t('our_purpose'))?></h2><p><?=e(t('our_purpose_text'))?></p><h2><?=e(t('platform_covers'))?></h2><p><?=e(t('platform_covers_text'))?></p><h2><?=e(t('editorial_approach'))?></h2><p><?=e(t('editorial_approach_text'))?></p><h2><?=e(t('important_notice'))?></h2><p><?=e(t('important_notice_text'))?></p></article><aside class="principles"><strong><?=e(t('platform_principles'))?></strong><ul><li><?=e(t('principle_pan_african'))?></li><li><?=e(t('principle_country'))?></li><li><?=e(t('principle_sources'))?></li><li><?=e(t('principle_versions'))?></li><li><?=e(t('principle_multilingual'))?></li></ul></aside></div></section>
<?php public_footer();exit;}

if($page==='subscribe'){
  public_header(t('subscribe'));page_hero(t('subscribe'),t('updates_title'),t('updates_text')); ?>
<section class="section"><div class="narrow"><form class="form-card" method="post"><?=csrf_field()?><input type="hidden" name="action" value="newsletter-subscribe"><input type="hidden" name="return_page" value="subscribe"><h2><?=e(t('subscribe'))?></h2><label><span><?=e(t('name'))?> <small>(<?=e(t('optional'))?>)</small></span><input name="name"></label><label><span><?=e(t('email'))?></span><input type="email" name="email" required></label><label class="check"><input type="checkbox" required><span><?=e(t('newsletter_consent'))?></span></label><button><?=e(t('subscribe'))?></button></form></div></section>
<?php public_footer();exit;}

if($page==='terms'||$page==='privacy'){
  $isTerms=$page==='terms';public_header($isTerms?t('terms'):t('privacy'));page_hero(t('platform'),$isTerms?t('terms'):t('privacy'),$isTerms?t('terms_intro'):t('privacy_intro')); ?>
<section class="section"><div class="narrow prose"><?php if($isTerms):?><h2><?=e(t('research_information_only'))?></h2><p><?=e(t('research_information_text'))?></p><h2><?=e(t('saved_research'))?></h2><p><?=e(t('saved_research_text'))?></p><h2><?=e(t('sources_copyright'))?></h2><p><?=e(t('sources_copyright_text'))?></p><h2><?=e(t('production_note'))?></h2><p><?=e(t('terms_review_text'))?></p><?php else:?><h2><?=e(t('information_collected'))?></h2><p><?=e(t('information_collected_text'))?></p><h2><?=e(t('cookies'))?></h2><p><?=e(t('cookies_text'))?></p><h2><?=e(t('purpose_retention'))?></h2><p><?=e(t('purpose_retention_text'))?></p><h2><?=e(t('production_note'))?></h2><p><?=e(t('privacy_review_text'))?></p><?php endif;?></div></section>
<?php public_footer();exit;}
