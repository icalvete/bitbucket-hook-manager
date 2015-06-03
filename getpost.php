<?php
$log = true;
$log_file = '/tmp/getpost.log';

$scheme = 'http';
$host = 'localhost:8080';

$allowed_branches = '/^(DEV)|(FZ-)/';

$post = '';
foreach ($_POST as $k => $v){
	$post .= "$k = $v\n";
}

$get = '';
foreach ($_GET as $k => $v){
	$get .= "$k = $v\n";
}

$json = json_decode($_POST['payload']);
$repo_name = $json->{'repository'}->{'name'};
$commits =  $json->{'commits'};
# Only the first (last in json) commit have the branch. In later is null.
$branch = $commits[count($commits)-1]->{'branch'};

if (!preg_match($allowed_branches, $branch, $matches, PREG_OFFSET_CAPTURE)){
	exit;
}

$job_name = isset($_GET['job_name']) ?: $repo_name;
$token = $_GET['token'];

# Chossing job_name by branch name
$job_name = $branch == 'DEV' ? $job_name : $job_name . '-fb';

$path_crumb = 'crumbIssuer/api/json';
$url_crumb = $scheme.'://'.$host.'/'.$path_crumb;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_crumb);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$json = json_decode(curl_exec($ch));
curl_close($ch);
$crumb =  $json->{'crumb'};


$path_launch_job = '/job/'.$job_name.'/buildWithParameters?token='.$token.'&BRANCH='.urlencode($branch);
$url_launch_job = $scheme.'://'.$host.'/'.$path_launch_job;
$post_data = '{"parameter": {"name":"BRANCH", "value":"'.$branch.'"}}';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_launch_job);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Content-Type: application/json',
	'Content-Length: ' . strlen($post_data))
);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('.crumb:'.$crumb));
curl_exec($ch);
curl_close($ch);

if ($log){
	$fp = fopen($log_file, 'a+');
	$now = date("Y-m-d H:i:s u", time());
	fwrite($fp,  "--------- '.$now.' ---------\n");
	fwrite($fp,  "--------- POST ---------\n");
	fwrite($fp, $post);
	fwrite($fp,  "--------- GET ---------\n");
	fwrite($fp, $get);
	fwrite($fp,  "--------- JENKINS---------\n");
	fwrite($fp, $url_launch_job);
	fwrite($fp,  "\n\n");
	fclose($fp);
}
?>
