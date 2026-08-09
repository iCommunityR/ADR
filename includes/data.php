<?php
function country_data(): array {
  return [
['DZA','Algeria','Northern Africa'],['AGO','Angola','Central Africa'],['BEN','Benin','Western Africa'],['BWA','Botswana','Southern Africa'],['BFA','Burkina Faso','Western Africa'],['BDI','Burundi','Eastern Africa'],['CMR','Cameroon','Central Africa'],['CPV','Cape Verde','Western Africa'],['CAF','Central African Republic','Central Africa'],['TCD','Chad','Central Africa'],['COM','Comoros','Eastern Africa'],['CIV','Côte d’Ivoire','Western Africa'],['COD','Democratic Republic of Congo','Central Africa'],['DJI','Djibouti','Eastern Africa'],['EGY','Egypt','Northern Africa'],['GNQ','Equatorial Guinea','Central Africa'],['ERI','Eritrea','Eastern Africa'],['SWZ','Eswatini','Southern Africa'],['ETH','Ethiopia','Eastern Africa'],['GAB','Gabon','Central Africa'],['GHA','Ghana','Western Africa'],['GIN','Guinea','Western Africa'],['GNB','Guinea-Bissau','Western Africa'],['KEN','Kenya','Eastern Africa'],['LSO','Lesotho','Southern Africa'],['LBR','Liberia','Western Africa'],['LBY','Libya','Northern Africa'],['MDG','Madagascar','Eastern Africa'],['MWI','Malawi','Eastern Africa'],['MLI','Mali','Western Africa'],['MRT','Mauritania','Western Africa'],['MUS','Mauritius','Eastern Africa'],['MAR','Morocco','Northern Africa'],['MOZ','Mozambique','Southern Africa'],['NAM','Namibia','Southern Africa'],['NER','Niger','Western Africa'],['NGA','Nigeria','Western Africa'],['COG','Republic of Congo','Central Africa'],['RWA','Rwanda','Eastern Africa'],['STP','São Tomé & Príncipe','Central Africa'],['SEN','Senegal','Western Africa'],['SYC','Seychelles','Eastern Africa'],['SLE','Sierra Leone','Western Africa'],['SOM','Somalia','Eastern Africa'],['ZAF','South Africa','Southern Africa'],['SSD','South Sudan','Eastern Africa'],['GMB','The Gambia','Western Africa'],['SDN','The Sudan','Northern Africa'],['TGO','Togo','Western Africa'],['TUN','Tunisia','Northern Africa'],['UGA','Uganda','Eastern Africa'],['TZA','United Republic of Tanzania','Eastern Africa'],['ZMB','Zambia','Southern Africa'],['ZWE','Zimbabwe','Southern Africa']
  ];
}
function country_alpha2(string $alpha3): string {
  static $map=[
    'DZA'=>'DZ','AGO'=>'AO','BEN'=>'BJ','BWA'=>'BW','BFA'=>'BF','BDI'=>'BI','CMR'=>'CM','CPV'=>'CV','CAF'=>'CF','TCD'=>'TD','COM'=>'KM','CIV'=>'CI','COD'=>'CD','DJI'=>'DJ','EGY'=>'EG','GNQ'=>'GQ','ERI'=>'ER','SWZ'=>'SZ','ETH'=>'ET','GAB'=>'GA','GHA'=>'GH','GIN'=>'GN','GNB'=>'GW','KEN'=>'KE','LSO'=>'LS','LBR'=>'LR','LBY'=>'LY','MDG'=>'MG','MWI'=>'MW','MLI'=>'ML','MRT'=>'MR','MUS'=>'MU','MAR'=>'MA','MOZ'=>'MZ','NAM'=>'NA','NER'=>'NE','NGA'=>'NG','COG'=>'CG','RWA'=>'RW','STP'=>'ST','SEN'=>'SN','SYC'=>'SC','SLE'=>'SL','SOM'=>'SO','ZAF'=>'ZA','SSD'=>'SS','GMB'=>'GM','SDN'=>'SD','TGO'=>'TG','TUN'=>'TN','UGA'=>'UG','TZA'=>'TZ','ZMB'=>'ZM','ZWE'=>'ZW'
  ];
  return $map[strtoupper($alpha3)]??'';
}

function country_region_slug(string $region): string {
  static $map=[
    'Northern Africa'=>'north-africa',
    'Central Africa'=>'central-africa',
    'Western Africa'=>'west-africa',
    'Southern Africa'=>'southern-africa',
    'Eastern Africa'=>'east-africa',
  ];
  return $map[trim($region)] ?? strtolower(str_replace(' ','-',trim($region)));
}
function country_region_from_slug(string $slug): ?string {
  $slug=strtolower(trim($slug));
  $aliases=[
    'north-africa'=>'Northern Africa','northern-africa'=>'Northern Africa','northern africa'=>'Northern Africa',
    'central-africa'=>'Central Africa','central africa'=>'Central Africa',
    'west-africa'=>'Western Africa','western-africa'=>'Western Africa','western africa'=>'Western Africa',
    'southern-africa'=>'Southern Africa','south-africa-region'=>'Southern Africa','southern africa'=>'Southern Africa',
    'east-africa'=>'Eastern Africa','eastern-africa'=>'Eastern Africa','eastern africa'=>'Eastern Africa',
  ];
  return $aliases[$slug] ?? null;
}
function common_law_codes(): array { return ['BWA','SWZ','GHA','KEN','LSO','LBR','MWI','MUS','NAM','NGA','SYC','SLE','ZAF','GMB','SDN','UGA','TZA','ZMB','ZWE']; }
function platform_sections(): array { return [
 'trade-integration'=>'Trade Integration ADR Regimes','national-legislation'=>'National ADR Legislation','adr-specific'=>'ADR-specific Frameworks','sector-frameworks'=>'Sector / Industry ADR Frameworks','international-status'=>'Status of International ADR Procedures','institutions'=>'ADR Institutions and Rules','case-law'=>'Arbitration Case Law','research'=>'Research and Commentary','news'=>'Recent Additions / News'
]; }
function document_types(): array { return ['act'=>'Act / Law','regulation'=>'Regulation / Rule / Decree','order'=>'Order / Notice','treaty'=>'Treaty','agreement'=>'Agreement','protocol'=>'Protocol','charter'=>'Charter','statute'=>'Statute','institution-rule'=>'Institutional Rule','case-decision'=>'Court Decision / Award','report'=>'Report / Commentary','other'=>'Other']; }
function regimes(): array { return [
 ['au','AU','African Union','Charters, conventions and statutes'],['afcfta','AfCFTA','African Continental Free Trade Area','Agreement and protocols'],['comesa','COMESA','Common Market for Eastern and Southern Africa','Treaty, protocols and agreements'],['eac','EAC','East African Community','Treaty, protocols and agreements'],['ecowas','ECOWAS','Economic Community of West African States','Treaty, protocols and agreements'],['sadc','SADC','Southern African Development Community','Treaty, protocols and agreements']
]; }
function primary_subjects(): array { return ['Arbitration principles','Arbitration agreement','Arbitrators','Applicable law','Stay of judicial proceedings','Arbitration proceedings','Arbitral award','Recognition and enforcement','Costs','Other']; }
function secondary_subjects(): array { return ['Agreement / arbitration clause','Consent','Dispute','Jurisdiction of courts','Reference','Appointment','Challenge','Powers','Forum','Choice of law','Arbitrable dispute','Party to arbitration agreement','Clause inoperative','Step in proceedings','Readiness and willingness to arbitrate','Application and procedure','Interim measures','Jurisdiction','Award','Appeal','Setting aside','Ultra vires','Misconduct of arbitrator','Public policy','Procedure','Recognition and enforcement','Review','Costs']; }
