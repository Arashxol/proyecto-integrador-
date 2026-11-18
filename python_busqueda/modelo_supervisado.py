"""
Modelo de Aprendizaje Supervisado - DIALAB LIS
Archivo unico Python - Datos desde BD laboratorio_pii

Ubicacion: python_busqueda/modelo_supervisado.py (misma carpeta que busqueda_heuristica.py)
BD: MySQL 127.0.0.1:3307 / laboratorio_pii
Tarea: Clasificacion binaria - Predecir si un resultado sera Validado (1) o Pendiente (0)
         a partir de datos del paciente + orden + analisis
Features: edad, sexo, categoria, tiempo_entrega_horas, precio, total, tipo_muestra
Modelo: RandomForestClassifier

Ejecutar: python python_busqueda/modelo_supervisado.py
Requiere: pip install mysql-connector-python pandas scikit-learn matplotlib
"""

import mysql.connector
import pandas as pd
import matplotlib.pyplot as plt
import numpy as np

from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder
from sklearn.metrics import accuracy_score, classification_report, confusion_matrix, ConfusionMatrixDisplay

# ==========================================================
# 1. CONEXION BD (misma que busqueda_heuristica.py)
# ==========================================================
DB_CONFIG = {
    "host": "127.0.0.1",
    "port": 3307,
    "user": "root",
    "password": "",
    "database": "laboratorio_pii"
}

def obtener_dataset():
    try:
        con = mysql.connector.connect(**DB_CONFIG)
        print("Conexion exitosa a laboratorio_pii")
        query = """
            SELECT
                p.edad,
                p.sexo,
                ta.categoria,
                ta.tiempo_entrega_horas,
                ta.precio,
                m.tipo_muestra,
                oa.total,
                r.estado as estado_resultado
            FROM resultados r
            JOIN muestras m ON r.muestra_id = m.id
            JOIN tipos_analisis ta ON m.tipo_analisis_id = ta.id
            JOIN ordenes_analisis oa ON m.orden_id = oa.id
            JOIN pacientes p ON oa.paciente_id = p.id
        """
        df = pd.read_sql(query, con)
        con.close()
        print(f"Registros obtenidos: {len(df)}")
        return df
    except Exception as e:
        print(f"Error al conectar/consultar: {e}")
        return pd.DataFrame()

# ==========================================================
# 2. MAIN
# ==========================================================
if __name__ == "__main__":
    print("=== Modelo Supervisado DIALAB LIS ===")
    df = obtener_dataset()
    if df.empty:
        print("Sin datos. Verifica que XAMPP MySQL 3307 este activo")
        exit()

    print("\n--- Vista previa ---")
    print(df.head())
    print("\nDistribucion estado_resultado:")
    print(df["estado_resultado"].value_counts())

    df["target"] = df["estado_resultado"].apply(lambda x: 1 if x == "Validado" else 0)

    if df["target"].nunique() < 2:
        print("\nADVERTENCIA: Solo una clase. Se genera variacion sintetica para demo.")
        df_extra = df.copy()
        df_extra["target"] = 1 - df_extra["target"]
        df = pd.concat([df, df_extra], ignore_index=True)

    features = ["edad", "sexo", "categoria", "tiempo_entrega_horas", "precio", "total", "tipo_muestra"]
    X = df[features].copy()
    y = df["target"]

    for col in ["sexo", "categoria", "tipo_muestra"]:
        le = LabelEncoder()
        X[col] = le.fit_transform(X[col].astype(str))

    print(f"\nDataset: {X.shape[0]} filas, {X.shape[1]} features")

    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.3, random_state=42, stratify=y)
    print(f"Train: {len(X_train)} | Test: {len(X_test)}")

    clf = RandomForestClassifier(n_estimators=100, random_state=42, max_depth=5)
    clf.fit(X_train, y_train)
    y_pred = clf.predict(X_test)

    acc = accuracy_score(y_test, y_pred)
    print(f"\nAccuracy: {acc:.3f}")
    print("\nClassification Report:")
    print(classification_report(y_test, y_pred, target_names=["Pendiente","Validado"], zero_division=0))

    cm = confusion_matrix(y_test, y_pred, labels=[0,1])
    print("Matriz de confusion:")
    print(cm)

    fig, axes = plt.subplots(1, 3, figsize=(18, 5))
    counts = df["estado_resultado"].value_counts()
    axes[0].bar(counts.index, counts.values, color=["#4CAF50","#FFC107"][:len(counts)])
    axes[0].set_title("Distribucion de clases")
    axes[0].set_ylabel("Cantidad")
    disp = ConfusionMatrixDisplay(confusion_matrix=cm, display_labels=["Pendiente","Validado"])
    disp.plot(ax=axes[1], colorbar=False, cmap="Blues")
    axes[1].set_title("Matriz de Confusion")
    importancias = clf.feature_importances_
    orden = np.argsort(importancias)
    axes[2].barh(np.array(features)[orden], importancias[orden], color="teal")
    axes[2].set_title("Importancia de variables")
    axes[2].set_xlabel("Importancia")
    plt.suptitle("Modelo Supervisado DIALAB - RandomForest (Validado vs Pendiente)", fontsize=13)
    plt.tight_layout()
    plt.show()

    ejemplo = X_test.iloc[0:1]
    prob = clf.predict_proba(ejemplo)[0]
    print(f"\nEjemplo: {ejemplo.to_dict(orient='records')[0]}")
    print(f"Prob Pendiente: {prob[0]:.2f} | Prob Validado: {prob[1]:.2f} => Predicho: {'Validado' if y_pred[0]==1 else 'Pendiente'}")
    print("\nModelo listo. Archivo unico: python_busqueda/modelo_supervisado.py")
