<div>
    <table class="table table-hover">
        <thead>
            <tr>
                <th width='200'>일자</th>
                <th width='100'>타입</th>
                <th>내역</th>

                <th width='200'>출금</th>
                <th width='200'>입금</th>
                <th width='200'>잔액</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($emoney as $item)
                <tr>
                    <td width='200'>{{ $item->created_at }}</td>
                    <td width='100'>{{ $item->type }}</td>
                    <td>{{ $item->description }}</td>

                    <td width='200'>{{ $item->withdraw }}</td>
                    <td width='200'>{{ $item->deposit }}</td>
                    <td width='200'>{{ $item->balance }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if (count($emoney) == 0)
        <div class="text-center py-4">
            <p class="text-muted">적립금 내역이 없습니다.</p>
        </div>
    @endif

    {{-- 페이지 네비게이션 --}}
    @if ($emoney)
        <div class="mt-4">
            {{ $emoney->links() }}
        </div>
    @endif
</div>
