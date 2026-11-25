import { Head, Link } from '@inertiajs/react';
import { Dashboard, Users, Shield, Calendar, ClipboardList, BarChart, LogOut, Menu, X, ChevronDown } from 'lucide-react';
import { useState } from 'react';

export default function AppLayout({ children, header }) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const user = $props().auth?.user || {};
  const role = user?.rol || 'Sin rol';

  const menuItems = [
    { name: 'Dashboard', icon: Dashboard, route: 'dashboard', iconColor: 'text-blue-500', bgColor: 'bg-blue-50' },
    { name: 'Pacientes', icon: Users, route: 'pacientes.index', iconColor: 'text-indigo-500', bgColor: 'bg-indigo-50' },
    { name: 'Órdenes', icon: ClipboardList, route: 'ordenes.pendientes', iconColor: 'text-blue-500', bgColor: 'bg-blue-50' },
    { name: 'Resultados', icon: BarChart, route: 'resultados.index', iconColor: 'text-green-500', bgColor: 'bg-green-50' },
    { name: 'Búsqueda Heurística', icon: Shield, route: 'deteccion.index', iconColor: 'text-purple-500', bgColor: 'bg-purple-50' },
    { name: 'Citas', icon: Calendar, route: 'citas.index', iconColor: 'text-amber-500', bgColor: 'bg-amber-50' },
    { name: 'Reportes', icon: BarChart, route: 'laboratorio.reportes', iconColor: 'text-red-500', bgColor: 'bg-red-50' },
    { name: 'Usuarios', icon: Users, route: 'admin.usuarios', iconColor: 'text-pink-500', bgColor: 'bg-pink-50' },
    { name: 'Logs', icon: ClipboardList, route: 'admin.logs', iconColor: 'text-gray-500', bgColor: 'bg-gray-50' },
    { name: 'Backups', icon: BarChart, route: 'admin.backups', iconColor: 'text-yellow-500', bgColor: 'bg-yellow-50' },
    { name: 'Cerrar sesión', icon: LogOut, route: 'logout', iconColor: 'text-red-500', bgColor: 'bg-red-50', isLogout: true },
  ];

  const activeRoute = $props().currentRoute || '';

  const roleGroups = {
    Administrador: ['Dashboard', 'Pacientes', 'Órdenes', 'Resultados', 'Búsqueda Heurística', 'Citas', 'Reportes', 'Usuarios', 'Logs', 'Backups'],
    Bioquimico: ['Dashboard', 'Órdenes', 'Resultados', 'Búsqueda Heurística', 'Citas'],
    Recepcionista: ['Dashboard', 'Pacientes', 'Órdenes', 'Citas'],
  };

  const visibleItems = menuItems.filter(item => {
    if (item.name === 'Cerrar sesión') return true;
    return roleGroups[role]?.includes(item.name) || false;
  });

  return (
    <>
      <Head title={`${$props().pageTitle || 'DIALAB'}`} />
      <div class="flex h-screen overflow-hidden">
        {/* Sidebar */}
        <aside className={`fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 ease-in-out ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'} border-r border-gray-200 bg-white shadow-lg`}>
          <div className="flex items-center justify-between h-14 px-6 border-b border-gray-200">
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">D</div>
              <span className="text-xl font-bold text-gray-800 tracking-tight">DIALAB</span>
            </div>
            <button className="lg:hidden p-1 rounded-md text-gray-400 hover:text-gray-700" onClick={() => setSidebarOpen(false)}>
              <X className="w-5 h-5" />
            </button>
          </div>
          
          <nav className="mt-6 space-y-1 px-3">
            {visibleItems.map((item) => {
              const isActive = activeRoute === item.route || $props().currentRoute?.includes(item.route);
              return (
                <Link
                  key={item.name}
                  href={item.route}
                  className={`
                    flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-200
                    ${isActive ? 'bg-blue-100 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'}
                  `}
                  onClick={() => item.name === 'Cerrar sesión' && setSidebarOpen(false)}
                >
                  <div className={`w-8 h-8 rounded-lg flex items-center justify-center ${item.bgColor} ${item.iconColor}`}>
                    <item.icon className="w-5 h-5" />
                  </div>
                  <span className={isActive ? 'font-semibold' : 'font-normal'}>{item.name}</span>
                </div>
              </a>
            );
          })}
          <div className="mt-auto p-4 border-t border-gray-100">
            <div className="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
              <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs">
                {user?.name?.charAt(0) || 'U'}
              </div>
              <div className="flex flex-col">
                <div className="text-sm font-semibold text-gray-800">{user?.name}</div>
                <div className="text-xs font-medium text-gray-600 capitalize">{role}</div>
              </div>
            </div>
          </div>
        </aside>

        {/* Mobile overlay */}
        {sidebarOpen && (
          <div className="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm" onClick={() => setSidebarOpen(false)} />
        )}

        {/* Main content */}
        <div className="flex-1 flex flex-col overflow-hidden">
          {/* Topbar */}
          <header className="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-6 z-30">
            <div className="flex items-center gap-3">
              <button className="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-700" onClick={() => setSidebarOpen(true)}>
                <Menu className="w-5 h-5" />
              </button>
              <h1 className="text-xl font-bold text-gray-800">{header ?? 'DIALAB'}</div>
            </div>

            <div className="flex items-center gap-4">
              <div className="text-right hidden md:block">
                <div className="text-xs font-medium text-gray-900">{new Date().toLocaleDateString('es-ES', { weekday: 'short' })}</div>
                <div className="text-xs font-medium text-gray-600">{new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}</div>
              </div>
              <div className="flex items-center gap-3">
                <div className="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold text-xs">
                  {user?.name?.charAt(0) || 'U'}
                </div>
                <div className="hidden sm:block flex flex-col text-right">
                  <div className="text-sm font-semibold text-gray-800">{user?.name}</div>
                  <div className="text-xs font-medium text-gray-600 capitalize">{role}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST" className="hidden sm:block">
                  <input name="_method" type="hidden" value="DELETE" />
                  <input name="_token" type="hidden" value={document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''} />
                  <button type="submit" className="text-sm text-red-600 hover:text-red-800 font-medium">
                    Cerrar sesión
                  </button>
                </form>
              </div>
            </div>
          </header>

          {/* Page content */}
          <main className="flex-1 overflow-y-auto p-6">
            {children}
          </main>
        </div>
      </div>
    </>
  );
}
