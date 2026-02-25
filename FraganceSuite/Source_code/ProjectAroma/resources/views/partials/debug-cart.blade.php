<script>
// Script de verificación - Solo para debug
window.debugCart = {
    view: function() {
        const cart = localStorage.getItem('aroma_cart');
        console.log('📦 Contenido del carrito:', cart ? JSON.parse(cart) : 'Carrito vacío');
        console.table(cart ? JSON.parse(cart) : {});
    },
    clear: function() {
        localStorage.removeItem('aroma_cart');
        console.log('🗑️  Carrito limpiado');
    },
    add: function(id, name, price) {
        const cart = JSON.parse(localStorage.getItem('aroma_cart') || '{}');
        cart[id] = { id, name, price, quantity: 1 };
        localStorage.setItem('aroma_cart', JSON.stringify(cart));
        console.log('✅ Producto agregado:', cart[id]);
    }
};

console.log('💡 Debug tools disponibles:');
console.log('- debugCart.view()  : Ver carrito actual');
console.log('- debugCart.clear() : Limpiar carrito');
console.log('- debugCart.add(id, name, price) : Agregar producto de prueba');
</script>
