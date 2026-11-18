"""
Búsqueda Heurística A* - Laboratorio DIALAB
Archivo Python genérico - Conectado a la BD del proyecto

BD: laboratorio_pii (MySQL 127.0.0.1:3307) - credenciales de .env
Tabla: muestras + JOIN tipos_analisis + ordenes_analisis
Incluye: conexión, heurística, A* y gráfico
Ejecutar: python python_busqueda/busqueda_heuristica.py
"""

import mysql.connector
import heapq
import networkx as nx
import matplotlib.pyplot as plt

# ==========================================================
# 1. CONEXIÓN A LA BD DEL PROYECTO
# ==========================================================
DB_CONFIG = {
    "host": "127.0.0.1",
    "port": 3307,
    "user": "root",
    "password": "",
    "database": "laboratorio_pii"
}

def obtener_muestras():
    """Extrae datos directamente de MySQL laboratorio_pii"""
    try:
        conexion = mysql.connector.connect(**DB_CONFIG)
        print("Conexión exitosa a la base de datos")
        cursor = conexion.cursor(dictionary=True)
        consulta = """
            SELECT
                m.id,
                m.codigo_muestra,
                m.tipo_muestra,
                m.estado,
                m.fecha_recoleccion,
                ta.nombre AS analisis,
                ta.categoria,
                ta.tiempo_entrega_horas,
                oa.codigo_orden,
                oa.fecha_solicitud,
                oa.fecha_entrega_estimada
            FROM muestras m
            INNER JOIN tipos_analisis ta ON m.tipo_analisis_id = ta.id
            INNER JOIN ordenes_analisis oa ON m.orden_id = oa.id
            WHERE m.estado = 'Recolectado'
            ORDER BY m.fecha_recoleccion ASC
        """
        cursor.execute(consulta)
        muestras = cursor.fetchall()
        cursor.close()
        conexion.close()
        return muestras
    except mysql.connector.Error as e:
        print(f"Error al conectar: {e}")
        return []

# ==========================================================
# 2. HEURÍSTICA Y COSTO
# ==========================================================
def heuristica(estado, muestras):
    costo = 0
    for m in muestras:
        if m["id"] not in estado:
            costo += m["tiempo_entrega_horas"]
    return costo

def costo_muestra(muestra):
    return muestra["tiempo_entrega_horas"]

# ==========================================================
# 3. ALGORITMO A*
# ==========================================================
def a_estrella(muestras):
    ids = [m["id"] for m in muestras]
    inicio = tuple()
    objetivo = tuple(ids)
    cola = []
    contador = 0
    h = heuristica(inicio, muestras)
    heapq.heappush(cola, (h, contador, inicio))
    g_score = {inicio: 0}
    padres = {}
    grafo = nx.DiGraph()
    grafo.add_node(inicio)
    while cola:
        f_actual, _, estado_actual = heapq.heappop(cola)
        if estado_actual == objetivo:
            camino = []
            estado = estado_actual
            while estado != inicio:
                camino.append(estado)
                estado = padres[estado]
            camino.append(inicio)
            camino.reverse()
            return camino, grafo
        for m in muestras:
            mid = m["id"]
            if mid in estado_actual:
                continue
            nuevo = estado_actual + (mid,)
            costo = costo_muestra(m)
            nuevo_g = g_score[estado_actual] + costo
            if nuevo not in g_score or nuevo_g < g_score[nuevo]:
                g_score[nuevo] = nuevo_g
                h = heuristica(nuevo, muestras)
                f = nuevo_g + h
                contador += 1
                heapq.heappush(cola, (f, contador, nuevo))
                padres[nuevo] = estado_actual
                grafo.add_node(nuevo)
                grafo.add_edge(estado_actual, nuevo, g=nuevo_g, h=h, f=f)
    return [], grafo

# ==========================================================
# 4. RESULTADO
# ==========================================================
def mostrar_resultado(camino, muestras):
    dic = {m["id"]: m for m in muestras}
    print("\n===================================")
    print(" ORDEN RECOMENDADO POR A*")
    print("===================================\n")
    for pos, estado in enumerate(camino):
        if len(estado) == 0:
            print("INICIO")
        else:
            m = dic[estado[-1]]
            print(f"{pos}. {m['codigo_muestra']} - {m['analisis']}")

# ==========================================================
# 5. GRÁFICO
# ==========================================================
def mostrar_grafo(grafo, camino, muestras):
    dic = {m["id"]: m for m in muestras}
    nombres = {}
    for estado in grafo.nodes:
        if len(estado) == 0:
            nombres[estado] = "INICIO"
        else:
            nombres[estado] = dic[estado[-1]]["codigo_muestra"]
    pos = nx.spring_layout(grafo, seed=42)
    plt.figure(figsize=(16, 10))
    nx.draw_networkx_nodes(grafo, pos, node_size=2500, node_color="lightblue", edgecolors="black")
    nx.draw_networkx_edges(grafo, pos, arrows=True, arrowsize=20, edge_color="gray")
    nx.draw_networkx_labels(grafo, pos, nombres, font_size=8)
    conexiones = [(camino[i], camino[i+1]) for i in range(len(camino)-1)]
    nx.draw_networkx_edges(grafo, pos, edgelist=conexiones, width=4, edge_color="red", arrows=True, arrowsize=25)
    etiquetas = nx.get_edge_attributes(grafo, "f")
    etiquetas_finales = {}
    for (o, d), f in etiquetas.items():
        datos = grafo[o][d]
        etiquetas_finales[(o, d)] = f"g={datos['g']}\nh={datos['h']}\nf={datos['f']}"
    nx.draw_networkx_edge_labels(grafo, pos, edge_labels=etiquetas_finales, font_size=7)
    plt.title("Búsqueda A* para determinar el orden de procesamiento de muestras")
    plt.axis("off")
    plt.tight_layout()
    plt.show()

# ==========================================================
# 6. MAIN
# ==========================================================
if __name__ == "__main__":
    muestras = obtener_muestras()
    if not muestras:
        print("No hay muestras pendientes de procesamiento.")
    else:
        print("\nMUESTRAS OBTENIDAS DE LA BASE DE DATOS:")
        for m in muestras:
            print(f"- {m['codigo_muestra']} | {m['analisis']} | {m['tipo_muestra']}")
        camino, grafo = a_estrella(muestras)
        mostrar_resultado(camino, muestras)
        print(f"\nNodos: {grafo.number_of_nodes()} | Aristas: {grafo.number_of_edges()}")
        mostrar_grafo(grafo, camino, muestras)
