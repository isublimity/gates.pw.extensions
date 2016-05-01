<?php
// silex
// example for service : gates.pw
//


// -------------------------------- simple post message ----------------------------------------
class gatespw
{
    private $url=false;

    public function __construct($url)
    {
        $this->url=$url;
    }
    public function send($message)
    {
        return $this->file_post_contents($this->url,["message"=>$message]);
    }

    private function file_post_contents($url, $data)
    {
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/json',
                'content' => json_encode($data)
            )
        );

        $context = stream_context_create($opts);
        return file_get_contents($url, false, $context);
    }
}

// -------------------------------- APP ----------------------------------------

$app = new Silex\Application();
use Symfony\Component\HttpFoundation\Request;
//
//
// #services add testpp —plaintext —url=http://php56-gatespw.rhcloud.com/testplaintext
// .testpp aaabbbcccc
//
$app->post('/testplaintext', function(Request $request) use($app) {

    $gatespw_body=$request->request->get('gatespw_body');
    $gatespw_callback=$request->request->get('gatespw_callback');

    $gates=new gatespw($gatespw_callback);


    $app->on(Symfony\Component\HttpKernel\KernelEvents::TERMINATE, function() use ($gates,$gatespw_body) {
        // start long process , warn in ddos
        sleep(60);
        //
        $gates->send(sha1($gatespw_body)." : ".$gatespw_body);

    });
    // answer as text
    return $app->json(["message"=>'get text:`'.$gatespw_body.'`']);

});

//
//
// #services add pool --url=http://php56-gatespw.rhcloud.com/pathtest --auth=aabbcc
// .pool aa vv
// .pool vv xx --title=xxyyzz

$app->post('/pathtest/{name}/{param}', function($name,$param,Request $request) use($app) {




    $title=$request->request->get('title');
    $auth=$request->request->get('auth');
    $callback=$request->request->get('gatespw_callback');

    if ($auth!=='aabbcc')
    {
        return $app->json(["auth"=>false]);
    }

    $gates=new gatespw($callback);


    $app->on(Symfony\Component\HttpKernel\KernelEvents::TERMINATE, function() use ($gates,$name,$title) {
        // start long process , warn in ddos
        sleep(5);
        $gates->send("name:".$name.' after '.$title);
    });



    $result=['status'=>'ok','example_answer'=>['host_time'=>date('H:i:s'),'title'=>$title,'name'=>$name,'param'=>$param]];

    return $app->json($result);

});

$app->before(function (Symfony\Component\HttpFoundation\Request $request) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
});


$app->run();
