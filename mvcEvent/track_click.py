import pymysql 
import sys
import json

def get_db_connection():
         return pymysql.connect(
             host='localhost',
             user='root',
             password='',
             database="clickngo_db",
             charset='utf8mb4',
             cursorclass=pymysql.cursors.DictCursor
         )

def track_click(user_id: int, event_id: int) -> bool:
         try:
             connection = get_db_connection()
             with connection.cursor() as cursor:
                 if not isinstance(user_id, int) or user_id <= 0:
                     raise ValueError("Invalid user_id")
                 sql_event = "SELECT category FROM evenements WHERE id = %s"
                 cursor.execute(sql_event, (event_id,))
                 event = cursor.fetchone()
                 if not event:
                     raise ValueError("Event not found")
                 sql_click = """
                     INSERT INTO user_clicks (user_id, event_id, category, click_time)
                     VALUES (%s, %s, %s, NOW())
                 """
                 cursor.execute(sql_click, (user_id, event_id, event['category']))
                 connection.commit()
                 return True
         except Exception as e:
             print(f"Error: {str(e)}", file=sys.stderr)
             return False
         finally:
             connection.close()

if __name__ == '__main__':
         if len(sys.argv) != 3:
             print(json.dumps({'success': False, 'error': 'Invalid arguments'}))
             sys.exit(1)
         try:
             user_id = int(sys.argv[1])
             event_id = int(sys.argv[2])
             result = track_click(user_id, event_id)
             print(json.dumps({'success': result}))
         except ValueError as e:
             print(json.dumps({'success': False, 'error': str(e)}))