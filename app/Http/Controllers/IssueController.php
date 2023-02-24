<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Gh_profiles;
use App\Models\Gh_accounts;
use App\Models\Repositories;
use App\Models\Issues;
use App\Models\Commits;
use App\Models\Pullrequests;
use Dotenv\Validator as DotenvValidator;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use DB;

function devide_time($datetime){
    // dd($datetime);
    $year=mb_substr($datetime,0,4);
    $month=mb_substr($datetime,5,2);
    $day=mb_substr($datetime,8,2);
    $hour=mb_substr($datetime,11,2);
    $min=mb_substr($datetime,14,2);
    $sec=mb_substr($datetime,17,2);
    $devided_time["year"]=$year;
    $devided_time["month"]=$month;
    $devided_time["day"]=$day;
    $devided_time["hour"]=$hour;
    $devided_time["min"]=$min;
    $devided_time["sec"]=$sec;
    return $devided_time;
}

function calendar($id){
  // dd($id);
  $issues=DB::table('issues')->where('repositories_id',$id)->get();
  // dd($issues);
  $started_title=array();
  $started_start=array();
  $finished_title=array();
  $finished_start=array();
  $finished_finish=array();
  foreach($issues as $issue){
    if($issue->start_at!==null){
      // 開始してる
      if($issue->close_flag===0){
      $devs=devide_time($issue->start_at);
      //  dd($devs);
      // dd($devs['year']."-".$devs['month']."-".$devs['day']." ".$devs['hour'].":".$devs['min']);
        // 終了してnai
        $started_title[]=$issue->title;
        $started_start[]=$devs['year']."-".$devs['month']."-".$devs['day']." ".$devs['hour'].":".$devs['min'];
      }else{
        // 終了してru
        $devs=devide_time($issue->start_at);
        $devf=devide_time($issue->close_date);
        $finished_title[]=$issue->title;
        $finished_start[]=$devs['year']."-".$devs['month']."-".$devs['day']." ".$devs['hour'].":".$devs['min'];
        $finished_finish[]=$devf['year']."-".$devf['month']."-".$devf['day']." ".$devf['hour'].":".$devf['min'];
      }
    }else{
      $distart[]=$issue;
    }
  }
  $s_count=count($started_title);
  $f_count=count($finished_title);
  // dd($started);
  return [ $started_title, $started_start,$finished_title,$finished_start,$finished_finish,$s_count,$f_count];
}

class IssueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $data=calendar($id);
        // dd($data);
      $issues = DB::table('issues')
        ->where('repositories_id',$id)
        ->join('gh_profiles','user_id', '=', 'gh_profiles.id')
        ->join('repositories', 'repositories_id' , '=', 'repositories.id')
        ->select('issues.*', 'gh_profiles.acunt_name','repositories.repos_name')
        ->get();
        // ddd($issues);

      $op_clos_ratios = array_fill(0,7,0);
      $op_start_ratios = array_fill(0,7,0);
      // ddd($op_clos_ratio);
      $ratios_cnt = 0;
      // date_default_timezone_set('UTC');
      $day = (int)date("Ymd") - 6;
      // ddd($day);
      $day_ratio = 0.0;

      foreach($op_clos_ratios as $ratios){
        // sum open_total and close_total
        $open_total = 0;
        $close_total = 0;
        $start_total = 0;
        foreach($issues as $issue){
          // init values

          $open_date = (int)str_replace('-','',substr($issue->open_date, 0, 10));
          $close_date = (int)str_replace('-','',substr($issue->close_date, 0, 10));
          $start_date = (int)str_replace('-','',substr($issue->start_at, 0, 10));

          // Issue の open数とclose数をカウントする
          if($open_date > $day){
            // まだ開いていないので計算しない
          }else if($day >= $open_date && $close_date >= $day || $day >= $open_date && $close_date == 0){
            $open_total++;
            if($day >= $start_date && $start_date != 0){
              $start_total++;
            }
          }else{
            $close_total++;
          }
          // dd($day,$open_date,$close_date);
        }

        $day_ratio = 100 / ($open_total + $close_total) * $open_total;
        $day_start_ratio = 100 / ($open_total + $close_total) * $start_total;
        // dd($day,$open_total,$close_total);
        // dd($day_ratio);
        // end open close count foreach
        $op_clos_ratios[$ratios_cnt] = round($day_ratio);
        $op_start_ratios[$ratios_cnt] = round($day_start_ratio);
        $day++;
        $ratios_cnt++;
      }

      $weeks = ["6day ago","5day ago","4day ago","3day ago","2day ago","1day ago","today"];


      return view('Gitissue_view',['issues'=>$issues,'ratios'=>$op_clos_ratios,'start_ratios'=>$op_start_ratios,'weeks'=>$weeks,'id'=>$id,'calendar'=>$data]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
