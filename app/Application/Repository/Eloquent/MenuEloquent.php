<?php
namespace App\Application\Repository\Eloquent;

use App\Application\Model\Menu;
use App\Application\Repository\InterFaces\MenuInterface;
use App\Application\Model\Item;
use Validator;
use UxWeb\SweetAlert\SweetAlert;


class MenuEloquent extends AbstractEloquent implements MenuInterface{

    protected $item;

    public function __construct(Menu $menu , Item $item)
    {
        $this->model = $menu;
        $this->item = $item;
    }

    public function getItemById($id){
        return $this->item->find($id)->toJson();
    }

    public function updateOneMenuItem($request){
        $update = $this->item->find($request->id);;
        $update->name = $request->name;
        $update->icon = $request->icon;
        $update->link = $request->link;
        $update->type = $request->type;
        $update->save();
    }

    public function updateMenuItems($request){
        $positions = json_decode($request->data);
        if(count($positions) > 0){
            foreach($positions as $parentKey => $position){
                if(is_object($position)){
                    $this->updateItems($position->id , 0 , $parentKey);
                }
                if(array_key_exists('children' , $position)){
                    $id = $position->id;
                    foreach($position->children as $key => $child){
                        $this->updateItems($child->id , $id , $key);
                    }
                }
            }
        }
    }
    protected function updateItems($id , $parentId = 0 , $order = 0){
         $this->item->where('id' , $id)->update(['parent_id' => $parentId , 'order' => $order]);
    }

    public function addNewItemToMenu($request){
        $valid = Validator::make($request->all(),$this->item->validation);
        if($valid->fails()){
            SweetAlert::error( 'Be sure the name is unique', 'Error');
            return redirect()->back();
        }
       $this->item->create($request->all());
        SweetAlert::success("Done Add Item to Menu" , 'Done');
        return redirect()->back();
    }


    public function getMenuById($id){
        if($id != null){
            $min = $this->model->find($id);
            return getMenu($min->name);
        }
        return null;
    }
}