<?php

namespace App\Http\Traits;

//use Illuminate\Http\Request;
//use App\User;
//use Illuminate\Support\Facades\Auth;
//use Illuminate\Support\Facades\DB;
//use League\CommonMark\Extension\Attributes\Node\Attributes;

trait ExportHelpers
{
/*    public function downloadSystemUsers(){
        //dd('in download all');
        $users = User::select('name','email',DB::raw('DATE_FORMAT(created_at,"%d-%b-%Y") as created_at'))
            ->get();
        //dd($users);
        $colHeaders = array('Name','Email ID', 'Added On');
        $listOfFields = array('name','email','created_at');
        $fileName = "SystemUsers.csv";
        $this->generateCSV($fileName, $colHeaders, $users, 3, $listOfFields);
        
        //return($users);
    }
*/
    public function generateCSV($fileName, $colHeaders, $data, $noOfFields, $listOfFields ){
        //$fileName is the output file name 
        //echo('in Export Controller');
        
        //dd($headers);
        $file = fopen('php://output', 'w');
        ob_start();

        fputcsv($file, $colHeaders);
        //dd($data);
        foreach ($data as $d){
            $d = json_decode(json_encode($d),true);
            //dd(gettype($d), $listOfFields[0]);
            for ($i = 0; $i<$noOfFields; $i++){
                
                $row[$i] = $d[$listOfFields[$i]];
            }
            fputcsv($file,$row);
        }

        $string = ob_get_clean();

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: application/octate-stream');
        header('Content-Disposition: attachment; filename='.$fileName);
        header('Content-Transfer-Encoding: binary');
        
        //fclose($file);

        exit($string);
        
    }    
}
