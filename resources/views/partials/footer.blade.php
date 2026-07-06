<footer>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-9 col-12">
                <ul class="footer-text">
                    <li><p class="mb-0">Copyright &copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.</p></li>
                    <li><a href="#">v{{ app(\Safarjaisur\AdminPanel\AdminPanel::class)->version() }}</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <ul class="footer-text text-end">
                    <li><a href="{{ route('sjadmin.dashboard') }}">Need Help <i class="ti ti-help"></i></a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
