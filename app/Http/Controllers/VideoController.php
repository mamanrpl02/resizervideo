<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class VideoController extends Controller
{
    public function index()
    {
        return view('video');
    }

    public function resize(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,mkv,mpeg|max:512000',
            'percentage' => 'required|integer|min:1|max:200',
            'watermark' => 'required|integer|min:0|max:1',
        ]);

        $video = $request->file('video');
        $tempPath = $video->getRealPath();

        // ===== FIX: Copy file ke folder storage yang aman =====
        $inputFileName = 'input_' . time() . '.' . $video->getClientOriginalExtension();
        $storedInputPath = storage_path("app/tmp/$inputFileName");

        if (!is_dir(storage_path("app/tmp"))) {
            mkdir(storage_path("app/tmp"), 0777, true);
        }

        copy($tempPath, $storedInputPath);

        // ===== Ambil resolusi =====
        $ffprobe = \FFMpeg\FFProbe::create();
        $stream  = $ffprobe->streams($storedInputPath)->videos()->first();

        $width  = $stream->get('width');
        $height = $stream->get('height');

        $p = $request->percentage / 100;

        $newWidth  = max(16, intval($width * $p));
        $newHeight = max(16, intval($height * $p));

        if ($newWidth % 2 != 0) $newWidth--;
        if ($newHeight % 2 != 0) $newHeight--;

        // Output
        $output = 'resized_' . time() . '.mp4';
        $outputPath = storage_path("app/public/$output");

        $ffmpeg = 'C:\Users\abdur\Downloads\ffmpeg-master-latest-win64-gpl\bin\ffmpeg.exe';

        $wmPath = public_path('wm.png');

        // ===== FFmpeg Command =====
        $wmPath = public_path('wm.png');

        // ===== FFmpeg Command =====
        if ($request->watermark == 1) {

            // Ukuran watermark mengikuti lebar video (15%)
            $wmWidth = intval($newWidth * 0.15);
            if ($wmWidth < 40) $wmWidth = 40; // Minimal 40px

            $filter = "
                [1:v]scale={$wmWidth}:-1[wm];
                [0:v]scale={$newWidth}:{$newHeight}[v];
                [v][wm]overlay=main_w-overlay_w-20 : main_h-overlay_h-(main_h*0.03)";

            $cmd = [
                $ffmpeg,
                '-i',
                $storedInputPath,
                '-i',
                $wmPath,
                '-filter_complex',
                $filter,
                '-c:v',
                'libx264',
                '-preset',
                'medium',
                '-crf',
                '23',
                '-pix_fmt',
                'yuv420p',
                '-c:a',
                'aac',
                '-movflags',
                '+faststart',
                $outputPath
            ];
        } else {
            $cmd = [
                $ffmpeg,
                '-i',
                $storedInputPath,
                '-vf',
                "scale={$newWidth}:{$newHeight}",
                '-c:v',
                'libx264',
                '-preset',
                'medium',
                '-crf',
                '23',
                '-pix_fmt',
                'yuv420p',
                '-c:a',
                'aac',
                '-movflags',
                '+faststart',
                $outputPath
            ];
        } 

        $process = new Process($cmd);
        // $process->run();

        // if (!$process->isSuccessful()) {
        //     throw new ProcessFailedException($process);
        // }

        $process->run();

        if (!$process->isSuccessful()) {
            dd($process->getErrorOutput());  // <-- tampilkan error FFmpeg
        }

        unlink($storedInputPath);

        return response()->download($outputPath)->deleteFileAfterSend();
    }
}
