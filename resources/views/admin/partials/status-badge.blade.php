<span class="a-pill a-pill--{{ $status->value ?? $status }}">{{ method_exists($status, 'label') ? $status->label() : $status }}</span>
