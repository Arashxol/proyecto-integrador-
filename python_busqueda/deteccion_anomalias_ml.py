"""
Detección de Anomalías en Resultados de Laboratorio - DIALAB LIS
=================================================================
Modelo: Isolation Forest (Machine Learning No Supervisado)
Conexión directa a BD laboratorio_pii (MySQL 127.0.0.1:3307)

Uso: python python_busqueda\deteccion_anomalias_ml.py
     O ejecutar desde Laravel vía exec()

Requisitos: pip install mysql-connector-python pandas scikit-learn matplotlib
"""

import mysql.connector
import pandas as pd
import numpy as np
import re
import json
import sys
import matplotlib.pyplot as plt
from sklearn.ensemble import IsolationForest
from sklearn.preprocessing import LabelEncoder
from sklearn.model_selection import train_test_split


# ============================================================
# 1. CONFIGURACIÓN BD (misma que .env)
# ============================================================
DB_CONFIG = {
    "host": "127.0.0.1",
    "port": 3307,
    "user": "root",
    "password": "",
    "database": "laboratorio_pii"
}


# ============================================================
# 2. OBTENER DATOS HISTÓRICOS DESDE LA BD
# ============================================================
def obtener_datos():
    """Extrae resultados validados de la BD con todos los campos necesarios"""
    try:
        c = mysql.connector.connect(**DB_CONFIG)
        print("Conexión exitosa a laboratorio_pii")
        q = """
            SELECT
                p.id as paciente_id,
                p.edad,
                p.sexo,
                ta.id as examen_id,
                ta.nombre AS examen,
                ta.categoria,
                ta.valores_referencia,
                ta.tiempo_entrega_horas,
                m.id as muestra_id,
                m.codigo_muestra,
                m.tipo_muestra,
                m.estado as estado_muestra,
                m.fecha_recoleccion,
                r.id as resultado_id,
                r.resultado AS resultado_texto,
                r.estado AS estado_resultado,
                r.fecha_validacion,
                oa.id as orden_id,
                oa.codigo_orden,
                oa.fecha_solicitud
            FROM resultados r
            JOIN muestras m ON r.muestra_id = m.id
            JOIN tipos_analisis ta ON m.tipo_analisis_id = ta.id
            JOIN ordenes_analisis oa ON m.orden_id = oa.id
            JOIN pacientes p ON oa.paciente_id = p.id
            WHERE r.estado = 'Validado'
            ORDER BY r.fecha_validacion DESC
        """
        df = pd.read_sql(q, c)
        c.close()
        print(f"Registros obtenidos: {len(df)}")
        return df
    except Exception as e:
        print(f"Error BD: {e}")
        return pd.DataFrame()


# ============================================================
# 3. PREPROCESAMIENTO DE DATOS
# ============================================================
def extraer_numero(texto):
    """Extrae el primer número encontrado en un texto (ej: '95 mg/dL' -> 95.0)"""
    if pd.isna(texto):
        return np.nan
    nums = re.findall(r"[\d.]+", str(texto))
    return float(nums[0]) if nums else np.nan


def extraer_rango(texto):
    """Extrae valores mínimo y máximo de una referencia (ej: '70 - 100' -> 70.0, 100.0)"""
    if pd.isna(texto):
        return np.nan, np.nan
    nums = re.findall(r"[\d.]+", str(texto))
    if len(nums) >= 2:
        return float(nums[0]), float(nums[1])
    elif len(nums) == 1:
        return float(nums[0]), float(nums[0])
    return np.nan, np.nan


def limpiar_datos(df):
    """Limpia y prepara el DataFrame para el modelo"""
    if df.empty:
        return df, None, None, None

    # Resultado numérico
    df["val"] = df["resultado_texto"].apply(extraer_numero)

    # Rango de referencia
    df[["rmin", "rmax"]] = df["valores_referencia"].apply(
        lambda x: pd.Series(extraer_rango(x))
    )

    # Desviación respecto al rango
    df["centro_ref"] = (df["rmin"] + df["rmax"]) / 2
    df["desvio_ref"] = (df["rmax"] - df["rmin"]) / 2
    df["desvio_relativo"] = np.where(
        df["desvio_ref"] > 0,
        abs(df["val"] - df["centro_ref"]) / df["desvio_ref"],
        0
    )

    # Fecha y componentes temporales
    df["fecha"] = pd.to_datetime(df["fecha_recoleccion"])
    df["dia_semana"] = df["fecha"].dt.dayofweek
    df["mes"] = df["fecha"].dt.month
    df["dia_del_mes"] = df["fecha"].dt.day

    # Encoding de variables categóricas
    le_sexo = LabelEncoder()
    df["s"] = le_sexo.fit_transform(df["sexo"].astype(str))

    le_examen = LabelEncoder()
    df["e"] = le_examen.fit_transform(df["examen"].astype(str))

    le_categoria = LabelEncoder()
    df["c"] = le_categoria.fit_transform(df["categoria"].astype(str))

    le_tipo_muestra = LabelEncoder()
    df["tm"] = le_tipo_muestra.fit_transform(df["tipo_muestra"].astype(str))

    print(f"Dataset preparado: {df.shape[0]} filas, {df.shape[1]} columnas")
    print(f"Valores numéricos válidos: {df['val'].notna().sum()}")
    return df, le_sexo, le_examen, le_categoria


# ============================================================
# 4. ENTRENAR MODELO ISOLATION FOREST
# ============================================================
def entrenar_modelo(df):
    """Entrena Isolation Forest para detectar anomalías"""
    features = [
        "edad", "s", "e", "c", "tm",
        "val", "rmin", "rmax", "centro_ref", "desvio_ref",
        "desvio_relativo", "dia_semana", "mes", "dia_del_mes",
        "tiempo_entrega_horas"
    ]

    d = df.dropna(subset=["val", "rmin", "rmax"]).copy()

    if len(d) < 5:
        print("Pocos datos para entrenar. Generando datos sintéticos...")
        d = generar_datos_sinteticos(df)

    X = d[features].values

    modelo = IsolationForest(
        n_estimators=200,
        contamination=0.05,  # Se espera 5% de anomalías
        random_state=42,
        max_samples='auto'
    )
    modelo.fit(X)

    d["anomalia"] = modelo.predict(X) == -1
    d["score"] = modelo.score_samples(X)

    n_anomalias = d["anomalia"].sum()
    print(f"Modelo entrenado: {len(d)} muestras")
    print(f"Anomalías detectadas: {n_anomalias} ({n_anomalias/len(d)*100:.1f}%)")

    return modelo, d, features, le_sexo, le_examen, le_categoria


def generar_datos_sinteticos(df):
    """Genera datos sintéticos si hay pocos registros reales"""
    np.random.seed(42)
    n = 300

    tipos = df["examen"].unique() if len(df) > 0 else [
        "Hemograma", "Glucosa en Sangre", "Colesterol Total",
        "Creatinina", "Ácido Úrico", "Glucosa en Orina"
    ]
    categorias = ["Hematología", "Bioquímica", "Uroanálisis", "Parasitología"]
    tipos_muestra = ["Sangre", "Orina", "Heces", "Saliva"]

    datos = {
        "edad": np.random.randint(18, 85, n),
        "sexo": np.random.choice(["Masculino", "Femenino"], n),
        "examen": np.random.choice(tipos, n),
        "categoria": np.random.choice(categorias, n),
        "tipo_muestra": np.random.choice(tipos_muestra, n),
        "tiempo_entrega_horas": np.random.choice([24, 48, 72], n),
    }

    # Generar resultados con distribución normal alrededor del valor de referencia
    resultados = []
    for i in range(n):
        if i < n * 0.95:  # 95% normales
            resultado = np.random.normal(100, 20)
        else:  # 5% anomalías (valores extremos)
            resultado = np.random.choice([
                np.random.normal(300, 50),   # Muy alto
                np.random.normal(10, 5),     # Muy bajo
                np.random.normal(500, 100)   # Extremo
            ])
        resultados.append(resultado)

    datos["resultado"] = resultados
    datos["valores_referencia"] = ["70 - 100"] * n
    datos["fecha_recoleccion"] = pd.date_range("2026-01-01", periods=n, freq="D")

    return pd.DataFrame(datos)


# ============================================================
# 5. DETECTAR ANOMALÍAS EN DATOS EXISTENTES
# ============================================================
def detectar_anomalias(modelo, df, features, le_sexo, le_examen, le_categoria):
    """Ejecuta predicción y marca anomalías"""
    d = df.dropna(subset=["val", "rmin", "rmax"]).copy()

    X = d[features].values
    predicciones = modelo.predict(X)
    scores = modelo.score_samples(X)

    d["es_anomalia"] = predicciones == -1
    d["score_anomalia"] = scores

    n_anomalias = d["es_anomalia"].sum()
    print(f"Análisis completado: {len(d)} registros evaluados")
    print(f"Anomalías detectadas: {n_anomalias} ({n_anomalias/len(d)*100:.1f}%)")

    return d


# ============================================================
# 6. PREDICCIÓN DE UN NUEVO RESULTADO
# ============================================================
def predecir_nuevo_resultado(
    modelo, le_sexo, le_examen, le_categoria,
    edad, sexo, tipo_examen, resultado_valor,
    ref_min, ref_max, categoria="General",
    tipo_muestra="Sangre", tiempo_entrega=24
):
    """
    Predice si un resultado es anómalo.
    Retorna: (es_anomalia: bool, score: float, mensaje: str)
    """
    centro_ref = (ref_min + ref_max) / 2
    desvio_ref = (ref_max - ref_min) / 2
    desvio_relativo = abs(resultado_valor - centro_ref) / max(desvio_ref, 0.1)

    hoy = pd.Timestamp.now()
    dia_semana = hoy.dayofweek
    mes = hoy.month
    dia_del_mes = hoy.day

    try:
        s = le_sexo.transform([sexo])[0]
    except:
        s = 0
    try:
        e = le_examen.transform([tipo_examen])[0]
    except:
        e = 0
    try:
        c = le_categoria.transform([categoria])[0]
    except:
        c = 0

    tm_map = {"Sangre": 0, "Orina": 1, "Heces": 2, "Saliva": 3, "Tejido": 4, "Otro": 5}
    tm = tm_map.get(tipo_muestra, 0)

    x = np.array([[
        edad, s, e, c, tm,
        resultado_valor, ref_min, ref_max, centro_ref, desvio_ref,
        desvio_relativo, dia_semana, mes, dia_del_mes, tiempo_entrega
    ]])

    pred = modelo.predict(x)[0]
    score = modelo.score_samples(x)[0]

    es_anomalia = pred == -1
    estado = "ANOMALÍA DETECTADA" if es_anomalia else "Resultado normal"

    print(f"\n{'='*50}")
    print(f"NUEVO RESULTADO EVALUADO:")
    print(f"  Paciente: {edad} años, {sexo}")
    print(f"  Examen: {tipo_examen} ({categoria})")
    print(f"  Resultado: {resultado_valor}")
    print(f"  Referencia: {ref_min} - {ref_max}")
    print(f"  Score de anomalía: {score:.4f}")
    print(f"  Veredicto: {estado}")
    print(f"{'='*50}\n")

    return es_anomalia, score, estado


# ============================================================
# 7. VISUALIZACIÓN
# ============================================================
def graficar(d):
    """Genera gráficos de las anomalías detectadas"""
    if d.empty or "es_anomalia" not in d.columns:
        print("No hay datos para graficar.")
        return

    n = d[~d["es_anomalia"]]
    a = d[d["es_anomalia"]]

    fig, axes = plt.subplots(2, 2, figsize=(16, 12))

    # 1. Resultado vs Centro de referencia
    ax = axes[0, 0]
    ax.scatter(n["centro_ref"], n["val"], c="#2563eb", alpha=0.5, s=40, label="Normal")
    ax.scatter(a["centro_ref"], a["val"], c="#dc2626", alpha=0.9, s=80, marker="X", label="Anomalía")
    ax.set_xlabel("Centro de referencia")
    ax.set_ylabel("Resultado numérico")
    ax.set_title("Resultado vs Rango de Referencia")
    ax.legend()

    # 2. Distribución de scores
    ax = axes[0, 1]
    ax.hist(d["score_anomalia"], bins=30, color="#64748b", edgecolor="white")
    if len(a) > 0:
        umbral = d[d["es_anomalia"]]["score_anomalia"].max()
        ax.axvline(umbral, color="#dc2626", linestyle="--", label="Umbral anomalía")
    ax.set_xlabel("Score de anomalía")
    ax.set_ylabel("Frecuencia")
    ax.set_title("Distribución de Scores de Anomalía")
    ax.legend()

    # 3. Resultado por examen
    ax = axes[1, 0]
    for exp_id in d["e"].unique():
        sub = d[d["e"] == exp_id]
        ax.scatter(sub.index, sub["val"], alpha=0.6, s=30, label=f"Examen {exp_id}")
    ax.scatter(a.index, a["val"], c="#dc2626", s=100, marker="X", label="Anomalía", zorder=5)
    ax.set_xlabel("Índice de registro")
    ax.set_ylabel("Resultado numérico")
    ax.set_title("Resultados por Examen")
    ax.legend(fontsize=8)

    # 4. Edad vs Resultado
    ax = axes[1, 1]
    ax.scatter(n["edad"], n["val"], c="#2563eb", alpha=0.5, s=40, label="Normal")
    ax.scatter(a["edad"], a["val"], c="#dc2626", alpha=0.9, s=80, marker="X", label="Anomalía")
    ax.set_xlabel("Edad del paciente")
    ax.set_ylabel("Resultado numérico")
    ax.set_title("Edad vs Resultado")
    ax.legend()

    plt.suptitle("Detección de Anomalías en Resultados de Laboratorio - DIALAB", fontsize=14, fontweight="bold")
    plt.tight_layout()
    plt.show()


# ============================================================
# 8. EJECUCIÓN PRINCIPAL
# ============================================================
if __name__ == "__main__":
    print("=" * 60)
    print("  DETECCIÓN DE ANOMALÍAS - DIALAB LIS")
    print("  Algoritmo: Isolation Forest")
    print("=" * 60 + "\n")

    # Paso 1: Obtener datos
    df = obtener_datos()

    if df.empty:
        print("\nNo se obtuvieron datos. Verifica la conexión a la BD.")
    else:
        # Paso 2: Preparar
        df, le_sexo, le_examen, le_categoria = limpiar_datos(df)

        # Paso 3: Entrenar
        modelo, d, features, le_sexo, le_examen, le_categoria = entrenar_modelo(df)

        # Paso 4: Detectar
        d = detectar_anomalias(modelo, d, features, le_sexo, le_examen, le_categoria)

        # Paso 5: Graficar
        graficar(d)

        # Paso 6: Ejemplos de predicción
        print("\n--- Ejemplos de predicción con nuevos resultados ---")
        predecir_nuevo_resultado(
            modelo, le_sexo, le_examen, le_categoria,
            edad=45, sexo="Femenino", tipo_examen="Glucosa en Sangre",
            resultado_valor=95, ref_min=70, ref_max=100,
            categoria="Bioquímica", tipo_muestra="Sangre"
        )
        predecir_nuevo_resultado(
            modelo, le_sexo, le_examen, le_categoria,
            edad=45, sexo="Femenino", tipo_examen="Glucosa en Sangre",
            resultado_valor=450, ref_min=70, ref_max=100,
            categoria="Bioquímica", tipo_muestra="Sangre"
        )

    print("\nProceso finalizado.")