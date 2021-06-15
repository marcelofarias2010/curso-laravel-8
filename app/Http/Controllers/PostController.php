<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUpdatePost;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    //
    public function index()
    {
        $posts = Post::orderBy('id', 'DESC')->paginate();
        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.posts.create');
    }

    public function store(StoreUpdatePost $request)
    {
        $data = $request->all();
        if ($request->image->isValid()) {
            //obtem o nome do arquivo com sua extensão
            $nameFile = Str::of($request->title)->slug('-') . "." . $request->image->getClientOriginalExtension();
            // salva o nome do titulo a imagem no diretório posts
            $image = $request->image->storeAs('posts', $nameFile);
            $data['image'] = $image;
        }
        Post::create($data);

        return redirect()->route('posts.index')->with('message', 'Post Criado com sucesso');
    }

    public function show($id)
    {
        //$post = Post::where('id',$id)->first();
        if (!$post = Post::find($id)) {
            return redirect()->route('posts.index');
        }

        return view('admin.posts.show', compact('post'));
    }

    public function destroy($id)
    {
        if (!$post = Post::find($id)) {
            return redirect()->route('posts.index');

            if (Storage::exists($post->image)) {
                Storage::delete($post->image);
            }
        }
        $post->delete();
        return redirect()->route('posts.index')->with('message', 'Post Deletado com sucesso');
    }

    public function edit($id)
    {
        //$post = Post::where('id',$id)->first();
        if (!$post = Post::find($id)) {
            return redirect()->back();
        }

        return view('admin.posts.edit', compact('post'));
    }

    public function update(StoreUpdatePost $request, $id)
    {
        //$post = Post::where('id',$id)->first();
        if (!$post = Post::find($id)) {
            return redirect()->back();
        }

        $data = $request->all();

        if ($request->image && $request->image->isValid()) {
            if (Storage::exists($post->image)) {
                Storage::delete($post->image);
            }
            //obtem o nome do arquivo com sua extensão
            $nameFile = Str::of($request->title)->slug('-') . "." . $request->image->getClientOriginalExtension();
            // salva o nome do titulo a imagem no diretório posts
            $image = $request->image->storeAs('posts', $nameFile);
            $data['image'] = $image;
        }

        $post->update($data);

        return redirect()->route('posts.index')->with('message', 'Post Atualizado com sucesso');
    }

    public function search(Request $request)
    {
        $filters = $request->except('_token');

        $posts = Post::where('title', 'LIKE', "%{$request->search}%")
            ->orWhere('content', 'LIKE', "%{$request->search}%")
            ->paginate(1);
        return view('admin.posts.index', compact('posts', 'filters'));
    }
}
