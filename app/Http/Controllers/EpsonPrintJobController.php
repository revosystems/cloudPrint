<?php

namespace App\Http\Controllers;

use App\PrintJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class EpsonPrintJobController extends Controller
{
    public function index()
    {
        $connectionType = request('ConnectionType');
        $id             = request('ID');

        $this->savePrinterStatus($id);

        if ($connectionType == 'GetRequest') {
            return $this->returnAvailableJobs($id);
        }

        if ($connectionType == 'SetResponse') {
            return $this->setResponse($id);
        }
        return response("");
    }

    private function savePrinterStatus($printerId)
    {
        try {
            Redis::set("CloudPrinter_".$printerId, Carbon::now()->toDateTimeString());
        } catch (\Exception $e) {
            Log::info("Error setting value to Redis: ".$e->getMessage());
        }
    }

    private function returnAvailableJobs($id)
    {
        $printJobs = PrintJob::pending()->where('uuid', $id)->get();

        if ($printJobs->count() == 0 ){
            return response("");
        }

        $printJobs->each->update(["status" => PrintJob::STATUS_PRINTING]);

        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?><PrintRequestInfo>" .
            $printJobs->pluck('job')->implode('') . "</PrintRequestInfo>";

        return response($xml)->withHeaders([
            'Content-Type' => 'text/xml; charset=UTF-8'
        ]);
    }

    private function setResponse($id)
    {
        logger(request('ResponseFile'));
        $xml = simplexml_load_string(request('ResponseFile'));
        if (count($xml->response) != 0) {
            $printJobs = PrintJob::printing()->whereUuid($id)->get();
            foreach ($xml->response as $response) {
                $printJob = $printJobs->shift();
                $printJob->update([
                    'status' => $response['success']->__toString() === 'true'
                        ? PrintJob::STATUS_PRINTED
                        : PrintJob::STATUS_ERROR
                ]);
                Log::info("Print job  with id {$printJob->id} finished with success: {$response['success']} and code: {$response['code']}");
            }
        }

        return response("");
    }
}
