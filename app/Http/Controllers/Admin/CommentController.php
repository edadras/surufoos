<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = Comment::with('commentable.translations')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')), fn ($q) => $q->orderByRaw("status = 'pending' desc"))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.comments.index', compact('comments'));
    }

    public function update(Request $request, Comment $comment)
    {
        $comment->update($request->validate(['status' => ['required', Rule::in(array_keys(Comment::STATUSES))]]));

        return back()->with('success', 'وضعیت دیدگاه به‌روزرسانی شد.');
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return back()->with('success', 'دیدگاه حذف شد.');
    }
}
