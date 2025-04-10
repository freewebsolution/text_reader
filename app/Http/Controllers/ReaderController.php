<?php

namespace App\Http\Controllers;

use App\Models\Reader;
use App\Http\Requests\StoreReaderRequest;
use App\Http\Requests\UpdateReaderRequest;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class ReaderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReaderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Reader $reader)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reader $reader)
    {
        return view('reader.upload');
    }

    /**
     * Update the specified resource in storage.
     */

    
    public function update(UpdateReaderRequest $request, Reader $reader)
    {
        if (!$request->hasFile('image')) {
            return back()->withErrors(['image' => 'Nessun file immagine è stato caricato.']);
        }
    
        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('images', $imageName, 'public');
        $fullImagePath = storage_path('app/public/' . $path);
        $preprocessedPath = $this->preprocessImage($fullImagePath);
    
        try {
            $imageText = (new TesseractOCR($preprocessedPath))
                ->lang('eng')
                ->run();
        } catch (\Exception $e) {
            return back()->withErrors(['image' => 'Errore OCR: ' . $e->getMessage()]);
        }
    
        // ✅ NON rimuove gli slash o accenti
        $imageText = mb_convert_encoding($imageText, 'UTF-8', 'auto');

        $imageText = preg_replace('/[^\p{L}\p{N}\p{P}\p{Zs}]/u', '', $imageText);

    
        // ✅ Va a capo dopo i punti, punti interrogativi o esclamativi
        $formattedText = preg_replace('/([\.!?])(\s|$)/u', "$1\n", $imageText);
    
        // 📄 Crea il documento Word
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText($formattedText, ['name' => 'Arial', 'size' => 12]);
    
        // 📁 Cartella per salvare i Word
        $wordDir = storage_path('app/public/word_files');
        if (!File::exists($wordDir)) {
            File::makeDirectory($wordDir, 0755, true);
        }
    
        // 📄 Nome file uguale all'immagine
        $fileName = pathinfo($imageName, PATHINFO_FILENAME) . '.docx';
        $filePath = $wordDir . '/' . $fileName;
    
        $phpWordWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $phpWordWriter->save($filePath);
    
        return view('reader.upload', [
            'image' => $formattedText,
            'filePath' => asset('storage/word_files/' . $fileName),
        ]);
    }
    
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reader $reader)
    {
        //
    }

    /**
     * Preprocess the image: grayscale and contrast.
     */
    protected function preprocessImage($fullImagePath)
    {
        // Verifica che l'immagine esista
        if (!File::exists($fullImagePath)) {
            throw new \Exception("Il file immagine non esiste: " . $fullImagePath);
        }
        if (!is_readable($fullImagePath)) {
            throw new \Exception("Il file non è leggibile: " . $fullImagePath);
        }

        // Carica l'immagine
        $image = imagecreatefromjpeg($fullImagePath);
        if (!$image) {
            throw new \Exception("Errore nel caricare l'immagine.");
        }

        // Converti in scala di grigi
        imagefilter($image, IMG_FILTER_GRAYSCALE);

        // Aumenta il contrasto
        imagefilter($image, IMG_FILTER_CONTRAST, -50); // Aggiusta il valore secondo necessità

        // Crea la cartella se non esiste
        $preprocessedDir = storage_path('app/public/images');
        if (!File::exists($preprocessedDir)) {
            File::makeDirectory($preprocessedDir, 0755, true); // Crea la cartella se non esiste
        }

        // Salva l'immagine preprocessata
        $preprocessedPath = $preprocessedDir . '/' . basename($fullImagePath);
        imagejpeg($image, $preprocessedPath);

        // Libera la memoria
        imagedestroy($image);

        return $preprocessedPath;
    }

    /**
     * Funzione per formattare il testo in modo più leggibile.
     */
    protected function formatText($text)
    {
        // Rimuove i caratteri non alfanumerici, tranne i ritorni a capo
        $text = preg_replace('/[^A-Za-z0-9\s\.\,\;\:\-\!\?\n]/', '', $text); 
    
        // Rimuove eventuali spazi extra tra le parole
        $text = preg_replace('/\s+/', ' ', $text); 
    
        // Aggiunge un ritorno a capo dopo ogni punto (o altro segno di punteggiatura)
        $text = preg_replace('/([.!?])\s*/', '$1\n', $text); // Aggiunge a capo dopo ogni punto, punto esclamativo, punto interrogativo
    
        // Rimuove eventuali \n da una visualizzazione HTML (non necessario per il file txt)
        $text = str_replace('\n', "\n", $text); 
    
        // Non usare nl2br() qui, perché non vogliamo <br /> nel file .txt
    
        return $text;
    }
    
}
