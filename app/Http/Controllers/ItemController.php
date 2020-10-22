<?php

namespace App\Http\Controllers;

use App\BlobItem;
use App\Classes;
use App\Downloads;
use App\Item;
use App\LinkItem;
use App\Parents;
use App\Teachers;
use App\User;
use App\VItem;
use App\VParents;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\File;
use phpDocumentor\Reflection\DocBlock\Tags\Link;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File as FacadesFile;
use Madnest\Madzipper\Madzipper;

date_default_timezone_set('Africa/Kigali');

class ItemController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function addItem(Request $request)
    {/*
        $user = Auth::user();
        $teacher = Teachers::all()->where('User_Id', '=', $user->User_Id);
        if (!$teacher) {
            if ($user->Profil_Id != 3)
                return $this->errorRes('Unauthorized.', 401);
        }
*/
        $title = $request->input('title');
        if (!$title) return $this->errorRes('Veuillez insérer un titre', 404);
        else if (strpos($title, '.') != false) return $this->errorRes('Veuillez ne pas insérer de point "." dans le titre', 401);
        $class = $request->input('class');
        if (!$class) return $this->errorRes('Veuillez indiquer de quelle classe il s\'agit', 404);
        $detail = $request->input('details');
        $type = $request->input('itemtype');
        if (!$type) return $this->errorRes('De quel type s\'agit-il ?', 404);

        //$blobitem = $request->input('blobitem');
        //if (!$blobitem || strlen($blobitem) < 1) return $this->errorRes('Veuillez insérer un fichier (jpg, ... autres que des vidéos)', 404);

        $classId = Classes::all()->where('Class', '=', $class)->pluck('Class_Id')->first();
        if (!$classId) return $this->errorRes(['Cette classe est introuvable', $classId, $class], 404);

        $text = explode('/', $type);
        $text = implode(' ', $text);

        //if (strpos($type, "image") === false) {
        //return $this->errorRes([isset($text["image"]),$text], 404);
        /**/
        // Check if there is a file
        if (!$request->hasFile('linkitem')) {
            return $this->errorRes('Il n\'y a pas de fichier', 404);
        }

        $linkitem = $request->file('linkitem');
        if (!$linkitem) return $this->errorRes('Il n\'y a pas de fichier', 404);

        $wholeDir = $this->filesPath . $class;

        // To create a folder for a new class
        if (!file_exists($wholeDir)) {
            mkdir($wholeDir, 0777, true);
        }

        $title = $title . '.' . $linkitem->extension();

        $item = Item::create([
            'Title' => $title,
            'details' => $detail,
            'Class_Id' => $classId,
            'type' => $type,
        ]);

        $title = $this->transformFilename($title);

        if (!$item) {
            return $this->errorRes('Un problème est survenue lors de l\'importation', 500);
        } else {
            $wholeDirLink = $wholeDir . '/' . $class . '-' . $item->Item_Id . '-' . $title;
            $linkitem->move($wholeDir, $class . '-' . $item->Item_Id . '-' . $title);
            DB::insert("call add_linkitem(?,?)", [$wholeDirLink, $item->Item_Id]);
            return $this->successRes('Le support a bien été importé');
        }
        //}
        /*
        $item = Item::create([
            'Title' => $title,
            'details' => $detail,
            'Class_Id' => $classId,
            'type' => $type,
        ]);

        
        //return $this->errorRes($item->Item_Id,404);
        if (!$item) return $this->errorRes('Le support n\'a pas pu être ajouté', 404);
        DB::insert("call add_blobitem(?,?)", [$blobitem, $item->Item_Id]);
        return $this->successRes('Le support a bien été importé');
        */
    }

    public function addLink(Request $request)
    {
        $title = $request->input('title');
        if (!$title) return $this->errorRes('Veuillez insérer un titre', 404);
        $class = $request->input('classe');
        if (!$class) return $this->errorRes('Veuillez indiquer de quelle classe il s\'agit', 404);
        $detail = $request->input('details');
        $type = "external-link";

        $linkitem = $request->input('linkitem');
        if (!$linkitem || strlen($linkitem) < 1) return $this->errorRes('Veuillez insérer un lien', 404);

        $classId = Classes::all()->where('Class', '=', $class)->pluck('Class_Id')->first();
        if (!$classId) return $this->errorRes(['Cette classe est introuvable', $classId, $class], 404);

        $item = Item::create([
            'Title' => $title,
            'details' => $detail,
            'Class_Id' => $classId,
            'type' => $type,
        ]);

        //return $this->debugRes(strpos($linkitem, "http"));
        if (strpos($linkitem, "http") === false) {
            $linkitem = 'http://' . $linkitem;
        }

        $wholeDir = $this->filesPath . $class;

        // To create a folder for a new class
        if (!file_exists($wholeDir)) {
            mkdir($wholeDir, 0777, true);
        }

        $wholeDirLink = $wholeDir . '/' . $class . '-' . $item->Item_Id . '-';
        FacadesFile::put($wholeDirLink . $title . '.txt', "Lien vers le support: \n$linkitem");

        //return $this->errorRes($item->Item_Id,404);
        if (!$item) return $this->errorRes('Le support n\'a pas pu être ajouté', 404);
        DB::insert("call add_linkitem(?,?)", [$linkitem, $item->Item_Id]);
        return $this->successRes('Le support a bien été importé');
    }

    public function downloadItem($itemId, $userId)
    {
        $user = User::all()->where('User_Id', '=', $userId)->first();
        if (!$user) return $this->errorRes(["Unauthorized."], 401);

        if (!$itemId) return $this->errorRes('De quel support il s\'agit ?', 404);

        $item = VItem::all()->where('Item_Id', '=', $itemId)->first();
        if (!$item) return $this->errorRes('Ce support n\'existe pas', 404);

        $fileName = $item->Title;

        $path = $item->Link;

        //return $this->debugRes([$fileName, $path]);

        if (!file_exists($path)) {
            return $this->errorRes(['Ce fichier n\'existe pas', $path], 404);
        }

        $download = Downloads::all()->where('User_Id', '=', $user->User_Id)->where('Item_Id', '=', $item->Item_Id);

        $checkParent = Parents::all()->where('User_Id', '=', $user->User_Id)->first();

        // We count download ONLY if it is a parent who downloads
        if ($checkParent) {
            if (sizeof($download) < 1) {
                Downloads::create([
                    'User_Id' => $user->User_Id,
                    'Item_Id' => $item->Item_Id,
                    'nbDownloads' => 1
                ]);
            } else {
                /**/
                $download->first()->fill([
                    'nbDownloads' => $download->first()->nbDownloads + 1
                ])->save();
                //return $this->debugRes($download->first()->nbDownloads);
            }
        }

        return $this->download($path, $fileName);
    }

    public function archiveItems()
    {
        $zipper = new Madzipper();
        $lastArchives = [DB::select("call archives()")[0]];
        $lA = [];
        foreach ($lastArchives as $key => $value) {
            $date = date('Y-m-d_H-i-s', strtotime($value->archivesDate));
            array_push($lA, $date);
        }
        array_push($lA, date_format(date_create(), "Y-m-d_H-i-s"));
        $zipFileName = "./Archives_$lA[0]_$lA[1].zip";
        //return $this->debugRes($zipFileName);
        foreach (glob("./*.zip") as $filename) {
            unlink($filename);
        }
        $path = './files/';
        $files = glob($path . '*');
        $tab = [];
        $zipper->make($zipFileName);
        $zipper->remove($zipper->listFiles());
        /**/
        foreach ($files as $key => $value) {
            $classe = realpath($value);
            if (is_dir($classe)) {
                $v = substr($value, strlen($path));
                //array_push($tab, glob($value.'/*'));
                $tab = array_merge($tab, [$v => glob($v . '/*')]);
                $zipper->folder($v)->add(glob($value . '/*'));
            } else {
                $zipper->add($value);
                $tab = array_merge($tab, ['other' => $value]);
            }
        }

        $zipper->close();
        DB::insert("call new_archives()");
        //return $this->debugRes($zipFileName);
        return response()->download($zipFileName, basename($zipFileName));
    }

    public function delArchives()
    {
        $zips = glob("./*.zip");
        $physicalFiles = glob('./files/*');
        $files = Item::all()->where('disabled', '=', 0);
        //return $this->debugRes($files);
        foreach ($files as $file) {
            $file->fill(['disabled' => 1])->save();
        }

        foreach ($zips as $filename) {
            unlink($filename);
        }

        foreach ($physicalFiles as $filename) {
            if (is_dir($filename)) {
                foreach (glob($filename . '/*') as $file) {
                    unlink($file);
                }
            } else unlink($filename);
        }

        if (sizeof($zips) < 1) return $this->successRes('Il n\'y a plus de fichier zip dans le serveur');
        if (sizeof($physicalFiles) < 1) return $this->successRes('Il n\'y a plus de fichier dans le serveur');
        return $this->errorRes('Des fichiers sont encore présent', 501);
    }

    public function resetFiles()
    {
        $today = date_format(date_create(), "Y-m-d");
        $files = DB::select("call todays_disabled_items()");
        foreach ($files as $file) {
            $file->fill(['disabled' => 0])->save();
        }
        return $this->debugRes($files);
    }

    public function editItem(Request $request)
    {
        //return $this->debugRes('debug');
        $itemId = $request->input('itemId');
        if (!$itemId) return $this->errorRes('De quel support s\'agit-il ?', 404);
        $item = Item::all()->where('Item_Id', '=', $itemId)->first();
        if (!$item) return $this->errorRes('Ce support n\'existe pas', 404);

        $title = $request->input('title');
        if (!$title || $title == "null") $title = $item->Title;
        $details = $request->input('details');
        if (!$details || $details == "null") $details = $item->details;

        $vItem = VItem::all()->where('Item_Id', '=', $itemId)->first();
        $class = Classes::all()->where('Class_Id', '=', $vItem->Class_Id)->first();
        //return $this->errorRes([$item->Title => $title, $item->details => $details, $vItem],404);

        if ($vItem->LInkItem_Id) {
            //return $this->errorRes('Link existe, donc pas de blob', 404);
            $newItem = null;
            if (strpos($vItem->Link, "http") !== false) {
                //return $this->debugRes($vItem);
                $link = $request->input('link');
                if (!$link) $link = $vItem->Link;
                //return $this->debugRes([$vItem->LInkItem_Id, $vItem->Item_Id, $link, $title, $details]);
                $update = Item::findOrFail($vItem->Item_Id)->fill(['Title' => $title, 'details' => $details])->save();
                if ($update) DB::update("call update_linkitem_file(?,?,?);", [$vItem->LInkItem_Id, $vItem->Item_Id, $link]);
                else return $this->errorRes('Un problème est survenu lors de la mise à jour des informations', 500);
                return $this->successRes('Les informations du lien ont bien été mis à jour');
            }

            if (!$request->hasFile('linkitem')) {
                //return $this->errorRes('Link n\'est pas modifié', 404);
                $link = $vItem->Link;
            } else {
                //return $this->errorRes('Link à modifier', 404);
                $linkitem = $request->file('linkitem');
                $wholeDir = $this->filesPath . $class->Class;

                // To create a folder for a new class
                if (!file_exists($wholeDir)) {
                    mkdir($wholeDir, 0777, true);
                }

                if (strpos($vItem->Title, '.') != false) {
                    //return $this->errorRes('Titre de link à modifier', 404);
                    $title = substr($vItem->Title, 0, strpos($vItem->Title, '.'));
                    $title = $title . '.' . $linkitem->extension();
                    //return $this->debugRes([$title, $linkitem->extension()]);
                }

                $newItem = Item::create([
                    'Title' => $title,
                    'details' => $details,
                    'Class_Id' => $vItem->Class_Id,
                    'type' => $vItem->Type,
                ]);

                $fileTitle = $this->transformFilename($title);

                $wholeLink = $wholeDir . '/' . $class->Class . '-' . $newItem->Item_Id . '-' . $fileTitle;

                //return $this->errorRes($class,404);

                $linkitemId = LinkItem::all()->where('Item_Id', '=', $vItem->Item_Id)->pluck('LInkItem_Id')->first();
                if (!$linkitemId) return $this->errorRes('Ce support est introuvable', 404);

                Item::findOrFail($vItem->Item_Id)->fill(['disabled' => 1])->save();

                $linkitem->move($wholeDir, $class->Class . '-' . $newItem->Item_Id . '-' . $fileTitle);

                DB::update("call update_linkitem_file(?,?,?);", [$linkitemId, $newItem->Item_Id, $wholeLink]);

                //return $this->successRes('Le fichier a bien été mis à jour');
            }

            if (strpos($vItem->Title, '.') != false) {
                //return $this->errorRes('Titre de link à modifier', 404);
                $extension = substr($vItem->Title, strpos($vItem->Title, '.'));
                if ($title !== $vItem->Title) $title = $title . $extension;
            }

            //return $this->errorRes([$vItem, $item, $title], 404);
            //DB::update("call update_linkitem_info(?,?,?);", [$itemId, $title, $details]);
            if (!$newItem) Item::findOrFail($vItem->Item_Id)->fill(['Title' => $title, 'details' => $details])->save();
            //return $this->errorRes([$newItem->first(),$newItem], 404);
            return $this->successRes('Les informations ont été mis à jour');
        }

        $blob = $request->input('blob');
        if (!$blob || $blob == "null") $blob = $vItem->File;
        Item::findOrFail($vItem->Item_Id)->fill(['Title' => $title, 'details' => $details])->save();
        DB::update("call update_pdfitem(?,?);", [$vItem->Item_Id, $blob]);
        return $this->successRes('Les informations ont été mis à jour');
    }

    public function getItems($classe)
    {
        if (!$classe) return $this->errorRes('De quel classe s\'agit-il ?', 404);

        $class_id = Classes::all()->where('Class', '=', $classe)->pluck('Class_Id')->first();
        if (!$class_id) return $this->errorRes('Cette classe n\'existe pas', 404);

        $items = VItem::all()->where('Class_Id', '=', $class_id)->where('disabled', '=', 0);
        if (!$items) return $this->errorRes('Il n\'y a pas de support pour ce cours', 404);

        return $this->successRes($items);
    }

    public function delItem(Request $request)
    {
        $item = $request->input('item_id');
        if (!$item) return $this->errorRes('De quel support s\'agit-il ?', 404);

        DB::update("call del_item(?)", [$item]);

        return $this->successRes('Le support a bien été supprimé');
    }
}
