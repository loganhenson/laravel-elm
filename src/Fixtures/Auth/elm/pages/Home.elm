module Home exposing (..)

import Auth.Layout exposing (authButtonClasses, authContainer, authStatus)
import Html exposing (Html, button, div, p, text)
import Html.Attributes exposing (class, type_)
import Html.Events exposing (onClick)
import Json.Decode exposing (Decoder, Value, decodeValue, nullable, string, succeed)
import Json.Decode.Pipeline exposing (required)
import Json.Encode
import LaravelElm exposing (Page, page)
import Routes exposing (post)


type alias Props =
    { status : Maybe String }


type alias State =
    {}


type alias Model =
    { props : Props
    , state : State
    }


type Msg
    = NewProps Value
    | Logout


decodeProps : Decoder Props
decodeProps =
    succeed Props
        |> required "status" (nullable string)


stateFromProps : Props -> State
stateFromProps props =
    {}


main : Page Model Msg
main =
    page
        { decodeProps = decodeProps
        , stateFromProps = stateFromProps
        , update = update
        , view = view
        , newPropsMsg = NewProps
        }


update : Msg -> Model -> ( Model, Cmd Msg )
update msg { props, state } =
    case msg of
        NewProps newProps ->
            ( { props = Result.withDefault props (decodeValue decodeProps newProps)
              , state = state
              }
            , Cmd.none
            )

        Logout ->
            ( { props = props, state = state }
            , post <|
                Json.Encode.object
                    [ ( "url", Json.Encode.string Routes.logout )
                    , ( "data", Json.Encode.object [] )
                    ]
            )


view : Model -> Html Msg
view { props, state } =
    authContainer "Dashboard"
        [ authStatus props.status
        , p [ class "mb-6" ] [ text "You are logged in!" ]
        , div []
            [ button [ class authButtonClasses, type_ "button", onClick Logout ]
                [ text "Log out" ]
            ]
        ]
