<?php

namespace App\Http\Controllers;

use App\Student;
use App\Students;
use App\User;
use App\VStudents;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getStudents($parentId)
    {
        $students = VStudents::all()->where('Parent_Id', '=', $parentId);
        if (!$students || sizeof($students) < 1) return $this->errorRes('Ce parent n\'a aucun enfant d\'enregistré', 404);
        return $this->successRes($students);
    }

    public function editParent(Request $request)
    {
        $user_Id = $request->input('user_Id');
        if (!$user_Id) return $this->errorRes('De qui s\'agit-il ?', 404);

        $user = User::all()->where('User_Id', '=', $user_Id);
        if (!$user) return $this->errorRes('Cet utilisateur n\'existe pas', 404);
        $user = $user->first();

        $firstname = $request->input('firstname');
        if (!$firstname) $firstname = $user->Firstname;
        $surname = $request->input('surname');
        if (!$surname) $surname = $user->Surname;
        $email = $request->input('email');
        if ($email) {
            $emailCheck = User::all()->where('EmailAddress', '=', $email);
            if(sizeof($emailCheck) > 0) return $this->errorRes('Cet adresse email existe déjà',404);
        } else {
            $email = $user->EmailAddress;
        }

        //return $this->debugRes([$email, $emailCheck]);

        $user->fill(['Firstname' => $firstname, 'Surname' => $surname, 'EmailAddress' => $email, 'Profil_Id' => 2])->save();
        //return $this->debugRes([$user->fill(['Firstname' => $firstname, 'Surname' => $surname, 'EmailAddress' => $email, 'Profil_Id' => 2])->save(), $user->EmailAddress]);
        return $this->successRes('Les informations ont bien été mis à jour');
    }

    public function editStudent(Request $request)
    {
        $student_Id = $request->input('student_Id');
        if (!$student_Id) return $this->errorRes('De quel élève s\'agit-il ?', 404);

        $student = Student::all()->where('Student_Id', '=', $student_Id)->first();
        //return $this->debugRes($student);
        $firstname = $request->input('firstname');
        if (!$firstname) $firstname = $student->Firstname;
        $surname = $request->input('surname');
        if (!$surname) $surname = $student->Surname;
        $bday = $request->input('birthdate');
        if (!$bday) $bday = $student->BirthDate;
        $parent_Id = $request->input('parent_Id');
        if (!$parent_Id) $parent_Id = $student->Parent_Id;
        $class_Id = $request->input('class_Id');
        if (!$class_Id) $class_Id = $student->Class_Id;

        $data = [
            'Firstname' => strtoupper($firstname),
            'Surname' => strtoupper($surname),
            'BirthDate' => $bday,
            'Parent_Id' => $parent_Id,
            'Class_Id' => $class_Id,
        ];

        $student->fill($data)->save();

        return $this->successRes('Les informations ont bien été mis à jour');
    }
}
