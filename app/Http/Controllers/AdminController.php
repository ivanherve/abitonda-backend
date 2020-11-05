<?php

namespace App\Http\Controllers;

//use App\Profile;
//use App\User;

use App\Classes;
use App\Item;
use App\Parents;
use App\VClasses;
use App\VParents;
use App\Profil;
use App\Student;
use App\Students;
use App\Teacher;
use App\Teachers;
use App\VTeachers;
use App\User;
use App\VStudents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getParents()
    {
        $parentList = VParents::all();
        if (sizeof($parentList) < 1) return $this->errorRes("Aucun parent n'est inscrit", 404);
        $user = Auth::user();
        if ($user->Profil_Id > 2) return $this->successRes($parentList);
        else {
            $parent = VParents::all()->where('User_Id', '=', $user->User_Id);
            if (!$parent) return $this->errorRes('Vous n\'êtes pas parent d\'élève', 404);
            $parentList = $parentList->where('Parent_Id', '=', $parent->first()->Parent_Id);
            //return $this->debugRes($parentList->first());
            $newList = [];
            foreach ($parentList as $key => $value) {
                array_push($newList, $value);
            }
            return $this->successRes($newList);
        }
    }

    public function addParent(Request $request)
    {
        $firstname = $request->input('firstname');
        if (!$firstname) return $this->errorRes('Veuillez introduire le prénom', 401);
        $surname = $request->input('surname');
        if (!$surname) return $this->errorRes('Veuillez introduire le nom de famille', 401);
        $email = $request->input('email');
        if (!$email) return $this->errorRes('Veuillez introduire une adresse email', 401);
        $password = $request->input('password');
        if (!$password) return $this->errorRes('Veuillez introduire un mot de passe', 401);

        $confpassword = $request->input('confpassword');
        $confpassword = $confpassword === $password;

        $password = $this->checkPwdStrength($password, $confpassword);

        if (!$password->original["status"]) return $this->errorRes($password->original["response"], 401);

        $password = $password->original["response"];

        //$profil_id = Profil::all()->where('Profil', '=', $profil)->pluck('Profil_Id')->first();
        $profil_id = 1;

        $emailCheck = User::all()->where('EmailAddress', '=', $email)->first();
        if ($emailCheck) return $this->errorRes("Cette adresse email est déjà utilisé par $emailCheck->Firstname $emailCheck->Surname", 404);

        DB::insert("call add_user(?,?,?,?,?)", [$firstname, $surname, $email, $password, $profil_id]);

        $user = User::all()->where('EmailAddress', '=', $email)->first();
        if (!$user) return $this->errorRes(["Le parent n'a pas été ajouté", User::all()], 404);

        DB::insert("call add_parent(?)", [$user->User_Id]);

        $parent = VParents::all()->where('User_Id', '=', $user->User_Id)->first();
        if (!$parent) return $this->errorRes("Un problème est survenu pour ajouter le parent", 404);

        return $this->successRes("$firstname $surname a bien été ajouté");
    }

    public function banParent(Request $request)
    {
        $parent_id = $request->input('parent_id');
        if (!$parent_id) return $this->errorRes('De quel parent s\'agit-il ?', 404);

        $parent = Parents::all()->where('Parent_Id', '=', $parent_id)->first();
        if (!$parent) return $this->errorRes('Ce parent n\'existe pas dans notre système', 404);

        $parent = User::all()->where('User_Id', '=', $parent->User_Id)->first();
        if (!$parent) return $this->errorRes('Ce parent est introuvable dans notre système', 404);

        $parent->fill(['Profil_Id' => 1])->save();

        if ($parent->Profil_Id === 1) {
            $students = Student::all()->where('Parent_Id', '=', $parent_id);
            if (!$students) return $this->successRes("$parent->Firstname $parent->Surname a bien été bannis");

            foreach ($students as $k => $s) {
                $s->fill(['disabled' => 1])->save();
            }

            return $this->successRes("$parent->Firstname $parent->Surname a bien été bannis, ainsi que ces enfants");
        }
    }

    public function resetParent(Request $request)
    {
        $parent_id = $request->input('parent_id');
        if (!$parent_id) return $this->errorRes('De quel parent s\'agit-il ?', 404);

        $parent = Parents::all()->where('Parent_Id', '=', $parent_id)->first();
        if (!$parent) return $this->errorRes('Ce parent n\'existe pas dans notre système', 404);

        $parent = User::all()->where('User_Id', '=', $parent->User_Id)->first();
        if (!$parent) return $this->errorRes('Ce parent est introuvable dans notre système', 404);

        $parent->fill(['Profil_Id' => 2])->save();

        if ($parent->Profil_Id !== 1) {
            $students = Student::all()->where('Parent_Id', '=', $parent_id);
            if (!$students) return $this->successRes("$parent->Firstname $parent->Surname a bien été réintégré");
            foreach ($students as $k => $s) {
                $s->fill(['disabled' => 0])->save();
            }
            return $this->successRes("$parent->Firstname $parent->Surname a bien été réintégré, ainsi que ces enfants");
        }
    }

    public function getTeachers()
    {
        $user = Auth::user();
        //return $this->debugRes($user);
        if ($user->Profil_Id > 2) {
            $teachersList = VTeachers::all();
            return $this->successRes($teachersList);
        } else {
            $teacherId = Teacher::all()->where('User_Id', '=', $user->User_Id)->pluck('Professor_Id')->first();
            $teachersList = VTeachers::all()->where('Professor_Id', '=', $teacherId);
            $newList = [];
            foreach ($teachersList as $key => $value) {
                array_push($newList, $value);
            }
            if (!$newList) return $this->errorRes("Vous n'avez aucun employée", 404);
            return $this->successRes($newList);
        }
    }

    public function addTeacher(Request $request)
    {
        $firstname = $request->input('firstname');
        if (!$firstname) return $this->errorRes('Veuillez introduire le prénom', 401);
        $surname = $request->input('surname');
        if (!$surname) return $this->errorRes('Veuillez introduire le nom de famille', 401);
        $email = $request->input('email');
        if (!$email) return $this->errorRes('Veuillez introduire une adresse email', 401);
        $password = $request->input('password');
        if (!$password) return $this->errorRes('Veuillez introduire un mot de passe', 401);
        $confpassword = $request->input('confpassword');

        $password = $this->checkPwdStrength($password, $confpassword);

        //return $this->debugRes($password->original);

        if (!$password->original["status"]) return $this->errorRes($password->original["response"], 401);

        $password = $password->original["response"];

        $profil = $request->input('profil');
        if (!$profil) return $this->errorRes('Veuillez introduire un profil', 401);

        $profil_id = Profil::all()->where('Profil', '=', $profil)->pluck('Profil_Id')->first();

        $emailCheck = User::all()->where('EmailAddress', '=', $email)->first();
        if ($emailCheck) return $this->errorRes("Cette adresse email est déjà utilisé", 404);

        DB::insert("call add_user(?,?,?,?,?)", [$firstname, $surname, $email, $password, $profil_id]);

        $user = User::all()->where('EmailAddress', '=', $email);
        if (!$user) return $this->errorRes("Le professeur n'a pas été ajouté", 404);
        //return $this->errorRes($user,404);
        foreach ($user as $key => $value) {
            $user = $value;
        }
        DB::insert("call add_professor(?)", [$user->User_Id]);

        $teacher = VTeachers::all()->where('User_Id', '=', $user->User_Id)->first();
        if (!$teacher) return $this->errorRes("Un problème est survenu pour ajouter le professeur", 404);

        return $this->successRes("$firstname $surname a bien été ajouté");
    }

    public function editTeacher(Request $request)
    {
        # code...
        $teacherId = $request->input('userId');
        if (!$teacherId) return $this->errorRes('De qui s\'agit-il ?', 404);
        $firstname = $request->input('firstname');
        $surname = $request->input('surname');
        $profil = $request->input('profil');
        $email = $request->input('email');

        $teacher = User::all()->where('User_Id', '=', $teacherId);
        $tProfil = VTeachers::all()->where('User_Id', '=', $teacherId)->pluck('Profil')->first();

        if (!$teacher) return $this->errorRes('Ce compte n\'existe pas', 404);

        $teacher = $teacher->first();

        if (!$firstname) $firstname = $teacher->Firstname;
        if (!$surname) $surname = $teacher->Surname;
        if (!$profil) $profil = $tProfil;
        $checkProfil = Profil::all()->where('Profil', '=', $profil);
        if (!$checkProfil) return $this->errorRes("Ce profil n'existe pas", 404);
        //return $this->debugRes([$request->input('profil'), $profil, $checkProfil->first()->Profil_Id, $tProfil]);
        if ($email) {
            $emailCheck = User::all()->where('EmailAddress', '=', $email);
            if (sizeof($emailCheck) > 0) return $this->errorRes('Cet adresse email est déjà utilisé', 404);
        } else {
            $email = $teacher->EmailAddress;
        }

        $data = ['Firstname' => $firstname, 'Surname' => $surname, 'EmailAddress' => $email, 'Profil_Id' => $checkProfil->first()->Profil_Id];

        $teacher->fill($data)->save();

        return $this->successRes('Les informations ont bien été mis à jour');
    }

    public function banTeacher(Request $request)
    {
        $userId = $request->input('userId');
        if (!$userId) return $this->errorRes("De qui s'agit-il ?", 404);
        $user = User::all()->where('User_Id', '=', $userId);
        if (!$user) return $this->errorRes("Cet utilisateur n'existe pas", 404);
        $user = $user->first();
        if ($user->Profil_Id > 3) return $this->errorRes("Vous ne pouvez pas bannir cet utilisateur", 401);
        $user->fill(['Profil_Id' => 1])->save();
        return $this->successRes("$user->Firstname $user->Surname a bien été bannis");
    }

    public function reHireTeacher(Request $request)
    {
        $userId = $request->input('userId');
        if (!$userId) return $this->errorRes("De qui s'agit-il ?", 404);
        $user = User::all()->where('User_Id', '=', $userId);
        if (!$user) return $this->errorRes("Cet utilisateur n'existe pas", 404);
        $user = $user->first();
        if ($user->Profil_Id > 3) return $this->errorRes("Vous ne pouvez pas bannir cet utilisateur", 401);
        $user->fill(['Profil_Id' => 2])->save();
        return $this->successRes("$user->Firstname $user->Surname a bien été réintégré");
    }

    public function getClassesPerTeacher($userId)
    {
        $user = User::all()->where('User_Id','=',$userId);
        if(!$user) return $this->errorRes('Cet utilistateur n\'existe pas', 404);
        $user = $user->first();
        $teacherId = Teacher::all()->where('User_Id', '=', $user->User_Id)->pluck('Professor_Id')->first();
        $classesList = VClasses::all()->where('disabled', '=', 0)->where('Professor_Id', '=', $teacherId);
        $newList = [];
        foreach ($classesList as $key => $value) {
            array_push($newList, $value->Class);
        }
        return $this->successRes($newList);
    }

    public function getClasses()
    {
        $user = Auth::user();
        //return $this->debugRes($user);
        if ($user->Profil_Id > 2) {
            $classesList = VClasses::all();
            return $this->successRes($classesList);
        } else {
            $teacherId = Teacher::all()->where('User_Id', '=', $user->User_Id)->pluck('Professor_Id')->first();
            $classesList = VClasses::all()->where('disabled', '=', 0)->where('Professor_Id', '=', $teacherId);
            $newList = [];
            foreach ($classesList as $key => $value) {
                array_push($newList, $value);
            }
            return $this->successRes($newList);
        }
    }

    public function addClass(Request $request)
    {
        $className = $request->input('class');
        if (!$className) return $this->errorRes('Veuillez introduire une classe', 404);
        $class = VClasses::all()->where('class', '=', $className)->first();
        if ($class) return $this->errorRes("Cette classe existe déjà", 401);
        $teacherId = $request->input('teacherId');
        $firstname = $request->input('firstname');
        $surname = $request->input('surname');
        $email = $request->input('email');
        $password = $request->input('password');
        $confpassword = $request->input('confPassword');
        $profil = 1;
        $isOld = $request->input('isOld');

        //return $this->debugRes([['class' => $className, 'teacher Id' => $teacherId, 'prénom' => $firstname, 'nom' => $surname, 'email' => $email, 'password' => $password, 'conf mdp' => $confpassword, 'profil' => $profil, 'ancien prof ?' => $isOld]]);
        if ($isOld == "true") {
            if (!$teacherId) return $this->errorRes("De quel professeur s\'agit-il ?", 404);
            $teacher = Teacher::all()->where('Professor_Id', '=', $teacherId)->first();
            if (!$teacher) return $this->errorRes('Ce professeur n\'existe pas dans notre système', 404);

            $class = Classes::create([
                'Class' => $className
            ]);

            $teacher->fill(['Class_Id' => $class->Class_Id])->save();

            $teacher = VTeachers::all()->where('Professor_Id', '=', $teacherId)->first();

            return $this->successRes("$teacher->Firstname $teacher->Surname enseigne maintenant la classe de $class->Class.");
        } else {
            if (!$firstname) return $this->errorRes('Veuillez insérer le prénom du professeur', 404);
            if (!$surname) return $this->errorRes('Veuillez insérer le nom de famille du professeur', 404);
            if (!$email) return $this->errorRes('Veuillez insérer l\'adresse e-mail du professeur', 404);
            if (!$password) return $this->errorRes('Veuillez insérer un mot de passe pour le professeur', 404);

            $password = $this->checkPwdStrength($password, $confpassword);

            if (!$password->original["status"]) return $this->errorRes($password->original["response"], 401);

            $password = $password->original["response"];

            DB::insert("call add_user(?,?,?,?,?)", [$firstname, $surname, $email, $password, $profil]);
            /*
            $newUser = User::create([
                'Firstname' => $firstname, 
                'Surname' => $surname, 
                'EmailAddress' => $email, 
                'Profil_Id' => $profil,
            ]);
            */

            $newUser = User::all()->where('EmailAddress', '=', $email)->first();
            //return $this->debugRes($newUser);

            $newTeacher = Teacher::create([
                //'Class_Id' => $class->Class_Id,
                'User_Id' => $newUser->User_Id,
            ]);

            $class = Classes::create([
                'Class' => $className,
                'Professor_Id' => $newTeacher->Professor_Id
            ]);

            return $this->successRes("$newUser->Firstname $newUser->Surname à bien été ajouté et enseigne maitenant la classe de $className");
        }
        //DB::insert("call add_class(?)", [$className]);
        return $this->successRes("La classe de $className a bien été ajouté!");
    }

    public function editClass(Request $request)
    {
        $class = $request->input('class');
        if (!$class) return $this->errorRes('De quelle classe s\'agit-il ?', 404);
        $teacherId = $request->input('teacherId');
        if (!$teacherId) return $this->errorRes('Quel professeur enseignera ce cours ?', 404);
        $activ = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);

        if ($activ) $activ = 1;
        else $activ = 0;

        $class = Classes::all()->where('Class', '=', $class)->first();
        if (!$class) return $this->errorRes('Cette classe n\'existe pas dans notre système', 404);
        $teacher = VTeachers::all()->where('Professor_Id', '=', $teacherId)->first();
        if (!$teacher) return $this->errorRes('Ce professeur n\'existe pas dans notre système', 404);
        
        $class->fill(['Professor_Id' => $teacher->Professor_Id, 'disabled' => $activ])->save();

        return $this->successRes("La classe de $class->Class a bien été modifié");
    }

    public function delClasses(Request $request)
    {
        $classId = $request->input('class_id');
        if (!$classId) return $this->errorRes('Quelle classe voulez-vous désactiver ?', 404);
        $class = Classes::all()->where('Class_Id', '=', $classId);
        if (!$class) return $this->errorRes("Cette classe n'existe pas", 404);

        $items = Item::all()->where('Class_Id', '=', $classId);

        if ($items) {
            for ($i = 0; $i < sizeof($items); $i++) {
                $items[$i]->fill(['disabled' => 1])->save();
            }
        }

        $class->first()->fill(['disabled' => 1])->save();
        return $this->successRes("La classe a bien été désactivé");
    }

    public function rebuildClass(Request $request)
    {
        $classId = $request->input('class_id');
        if (!$classId) return $this->errorRes('Quelle classe voulez-vous réactiver ?', 404);
        $class = Classes::all()->where('Class_Id', '=', $classId);
        if (!$class) return $this->errorRes("Cette classe n'existe pas", 404);

        $items = Item::all()->where('Class_Id', '=', $classId);

        if ($items) {
            for ($i = 0; $i < sizeof($items); $i++) {
                $items[$i]->fill(['disabled' => 0])->save();
            }
        }

        $class->first()->fill(['disabled' => 0])->save();
        return $this->successRes("La classe a bien été réactivé");
    }

    public function getStudents()
    {
        $studentList = VStudents::all();
        if (sizeof($studentList) < 1) return $this->errorRes("Il n'y a pas d'étudiant", 404);

        $user = Auth::user();

        if ($user->Profil_Id > 2) return $this->successRes($studentList);
        else {
            $parent = VParents::all()->where('User_Id', '=', $user->User_Id);
            if (!$parent) return $this->errorRes('Vous n\'êtes pas parent d\'élève', 404);
            $studentList = $studentList->where('Parent_Id', '=', $parent->first()->Parent_Id);
            $newList = [];
            foreach ($studentList as $key => $value) {
                array_push($newList, $value);
            }
            return $this->successRes($newList);
        }
    }

    public function addStudent(Request $request)
    {
        $firstname = $request->input('firstname');
        if (!$firstname) return $this->errorRes("Veuillez entrer un prénom", 404);
        $surname = $request->input('surname');
        if (!$surname) return $this->errorRes("Veuillez entrer un nom de famille", 404);
        $birthdate = $request->input('birthdate');
        if (!$birthdate) return $this->errorRes("Veuillez entrer une date de naissance", 404);
        $parent_id = $request->input('parent_id');
        if (!$parent_id) return $this->errorRes("Veuillez entrer l'identifiant du parent", 404);
        $class = $request->input('class');
        if (!$class) return $this->errorRes("Veuillez entrer la classe", 404);

        $classCheck = VClasses::all()->where('Class', '=', $class)->first();
        if (!$classCheck) return $this->errorRes('Cette classe n\'existe pas', 404);
        //return $this->errorRes($classCheck, 404);
        DB::insert("call add_student(?,?,?,?,?);", [strtoupper($firstname), strtoupper($surname), $birthdate, $parent_id, $classCheck->Class_Id]);
        /*
        $student = Students::create([
            'firstname' => $firstname,
            'surname' => $surname,
            'birthdate' => $birthdate,
            'parent_id' => $parent_id,
            'class_id' => $classCheck->Class_Id
        ]);
*/
        return $this->successRes("$firstname $surname a bien été ajouté");
    }

    public function banStudent(Request $request)
    {
        $studentId = $request->input('student_id');
        if (!$studentId) return $this->errorRes('Quel élève voulez-vous bannir ?', 404);
        $student = Student::all()->where('Student_Id', '=', $studentId);
        if (!$student) return $this->errorRes("Cet élève n'existe pas", 404);
        $student = $student->first();
        $student->fill(['disabled' => 1])->save();
        return $this->successRes("$student->Firstname $student->Surname a bien été bannis");
    }

    public function resetStudent(Request $request)
    {
        $studentId = $request->input('student_id');
        if (!$studentId) return $this->errorRes('Quel élève voulez-vous réintégrer ?', 404);
        $student = Student::all()->where('Student_Id', '=', $studentId);
        if (!$student) return $this->errorRes("Cet élève n'existe pas", 404);
        $student = $student->first();
        $student->fill(['disabled' => 0])->save();
        return $this->successRes("$student->Firstname $student->Surname a bien été réintégré");
    }

    public function getProfiles()
    {
        $profiles = Profil::all()->where('Profil_Id', '!=', 1)->where('Profil_Id', '!=', 4);
        $newList = [];
        foreach ($profiles as $key => $value) {
            array_push($newList, $value);
        }
        return $this->successRes($newList);
    }
}
