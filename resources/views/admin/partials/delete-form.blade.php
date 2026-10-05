<form action="{{ $action }}" method="post" class="a-inline" data-confirm="{{ $confirm ?? 'از حذف این مورد مطمئن هستید؟' }}">
    @csrf @method('DELETE')
    <button type="submit" class="a-btn a-btn--danger a-btn--sm">{{ $label ?? 'حذف' }}</button>
</form>
