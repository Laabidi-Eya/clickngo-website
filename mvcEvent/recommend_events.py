import pymysql
import json
from collections import Counter
from typing import List, Dict, Optional
from datetime import datetime, timedelta
import sys 
import os
# Set the working directory to the script's directory
script_dir = os.path.dirname(os.path.abspath(__file__))
def get_db_connection():
    """Establish a connection to the MySQL database."""
    return pymysql.connect(
        host='localhost',
        user='root',
        password='',
        database="click'n'go",
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )

def track_click(user_id: int, event_id: int) -> bool:
    """Track a user click on an event."""
    try:
        connection = get_db_connection()
        with connection.cursor() as cursor:
            # Validate user_id and fetch event category
            if not isinstance(user_id, int) or user_id <= 0:
                raise ValueError("Invalid user_id")
            sql_event = "SELECT category FROM evenements WHERE id = %s"
            cursor.execute(sql_event, (event_id,))
            event = cursor.fetchone()
            if not event:
                raise ValueError("Event not found")

            # Insert click record
            sql_click = """
                INSERT INTO user_clicks (user_id, event_id, category, click_time)
                VALUES (%s, %s, %s, NOW())
            """
            cursor.execute(sql_click, (user_id, event_id, event['category']))
            connection.commit()
            return True
    except Exception as e:
        print(f"Error tracking click: {str(e)}", file=sys.stderr)
        return False
    finally:
        connection.close()

def get_user_reservations(user_id: Optional[int] = None) -> List[Dict]:
    """Fetch reservations for a user or for NULL user_id if user_id is None."""
    try:
        connection = get_db_connection()
        with connection.cursor() as cursor:
            if user_id is not None:
                sql = """
                    SELECT e.id, e.category, e.name, e.date, e.price, e.image_url
                    FROM chaise c
                    JOIN evenements e ON c.event_id = e.id
                    WHERE c.statut = 'reserve' AND c.id_user = %s
                    GROUP BY e.id
                """
                cursor.execute(sql, (user_id,))
            else:
                sql = """
                    SELECT e.id, e.category, e.name, e.date, e.price, e.image_url
                    FROM chaise c
                    JOIN evenements e ON c.event_id = e.id
                    WHERE c.statut = 'reserve' AND c.id_user IS NULL
                    GROUP BY e.id
                """
                cursor.execute(sql)
            return cursor.fetchall()
    finally:
        connection.close()

def get_user_clicks(user_id: Optional[int] = None) -> List[Dict]:
    """Fetch user clicks from the last 30 days."""
    try:
        connection = get_db_connection()
        with connection.cursor() as cursor:
            if user_id is not None:
                sql = """
                    SELECT category, COUNT(*) as click_count
                    FROM user_clicks
                    WHERE user_id = %s
                    AND click_time > %s
                    GROUP BY category
                """
                thirty_days_ago = (datetime.now() - timedelta(days=30)).strftime('%Y-%m-%d %H:%M:%S')
                cursor.execute(sql, (user_id, thirty_days_ago))
            else:
                return []
            return cursor.fetchall()
    finally:
        connection.close()

def get_available_events() -> List[Dict]:
    """Fetch all events with available seats, ordered by date."""
    try:
        connection = get_db_connection()
        with connection.cursor() as cursor:
            sql = """
                SELECT id, category, name, price, image_url AS imageUrl, 
                       total_seats, reserved_seats,
                       (total_seats - reserved_seats) AS available_seats,
                       date
                FROM evenements
                WHERE total_seats > reserved_seats
                AND date > NOW()
                ORDER BY date ASC
            """
            cursor.execute(sql)
            return cursor.fetchall()
    finally:
        connection.close()

def generate_recommendations(user_id: Optional[int] = None) -> List[Dict]:
    """
    Generate event recommendations based on user reservations and clicks.
    For new users (no reservations or user_id=None), return one event per category.
    For existing users, score events based on reservations, clicks, availability, and recency.
    Exclude events the user has already reserved.
    """
    categories = ['sportif', 'culturel', 'culinaire', 'musique', 'charite']
    recommendations = []

    if user_id is None or not isinstance(user_id, int) or user_id <= 0:
        # New user: recommend one event from each category
        for category in categories:
            try:
                connection = get_db_connection()
                with connection.cursor() as cursor:
                    sql = """
                        SELECT id, name, price, image_url AS imageUrl
                        FROM evenements
                        WHERE category = %s
                        AND total_seats > reserved_seats
                        AND date > NOW()
                        ORDER BY date DESC
                        LIMIT 1
                    """
                    cursor.execute(sql, (category,))
                    event = cursor.fetchone()
                    if event:
                        recommendations.append({
                            'category': category,
                            'event': {
                                'id': event['id'],
                                'name': event['name'],
                                'price': event['price'],
                                'image': event['imageUrl'],
                                'available_seats': None  # Not calculated for new users
                            },
                            'image': event['imageUrl']
                        })
            finally:
                connection.close()
        return recommendations

    # Existing user: score-based recommendations
    reservations = get_user_reservations(user_id)
    clicks = get_user_clicks(user_id)
    events = get_available_events()

    # Get IDs of events the user has reserved
    reserved_event_ids = [res['id'] for res in reservations]

    # Calculate category scores
    category_scores = Counter()
    for res in reservations:
        category_scores[res['category']] += 10  # High weight for reservations
    for click in clicks:
        category_scores[click['category']] += click['click_count'] * 3  # Medium weight for clicks

    # Score events, excluding reserved events
    event_scores = []
    current_time = datetime.now()
    for event in events:
        if event['id'] in reserved_event_ids:
            continue  # Skip events the user has already reserved
        score = 0
        # Category match
        if event['category'] in category_scores:
            score += category_scores[event['category']]
        # Availability bonus
        availability_ratio = event['available_seats'] / event['total_seats']
        score += availability_ratio * 5
        # Recency bonus
        event_date = datetime.strptime(str(event['date']), '%Y-%m-%d %H:%M:%S')
        days_until_event = (event_date - current_time).days
        if days_until_event <= 30:
            score += 3 * (1 - days_until_event / 30)

        event_scores.append({
            'event': {
                'id': event['id'],
                'name': event['name'],
                'price': event['price'],
                'image': event['imageUrl'],
                'available_seats': event['available_seats']
            },
            'category': event['category'],
            'score': score
        })

    # Sort and limit to top 5
    event_scores.sort(key=lambda x: x['score'], reverse=True)
    recommendations = [
        {
            'category': item['category'],
            'event': item['event'],
            'image': item['event']['image']
        } for item in event_scores[:5]
    ]

    return recommendations
def main(user_id: Optional[int] = None):
    """Main function to generate and output recommendations as JSON."""
    try:
        recommendations = generate_recommendations(user_id)
        print(json.dumps({
            'success': True,
            'recommendations': recommendations
        }))
    except Exception as e:
        print(json.dumps({
            'success': False,
            'error': str(e)
        }))

if __name__ == '__main__':
    import sys
    user_id = int(sys.argv[1]) if len(sys.argv) > 1 and sys.argv[1].isdigit() else None
    main(user_id)