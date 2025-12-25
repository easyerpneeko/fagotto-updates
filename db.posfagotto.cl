$TTL 86400
@   IN  SOA ns1.posfagotto.cl. admin.posfagotto.cl. (
        2025122201  ; Serial (ACTUALIZADO - hoy 22 dic)
        3600        ; Refresh
        1800        ; Retry
        604800      ; Expire
        86400 )     ; Minimum TTL

; Servidores de nombres
@       IN  NS  ns1.posfagotto.cl.
@       IN  NS  ns2.posfagotto.cl.

; Registros A (IP NUEVA)
ns1         IN  A   200.29.153.154
ns2         IN  A   200.29.153.154
@           IN  A   200.29.153.154
www         IN  A   200.29.153.154
apimercado  IN  A   200.29.153.154
